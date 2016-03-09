#!/usr/bin/python
import mysql.connector
from Queue import Queue
from threading import Thread
import signal, os, socket
import time, sys
import argparse
import netmiko
import re
import logging
from isc_dhcp_leases.iscdhcpleases import Lease, IscDhcpLeases

def stop_handler(signum,frame):
  global run
  run = False

signal.signal(signal.SIGINT,stop_handler)

def parse_args():
  parser = argparse.ArgumentParser(description="",fromfile_prefix_chars='@')
  parser.add_argument('--db-user','-u', help="Database username",required=True)
  parser.add_argument('--db-pass','-p', help="Database password")
  parser.add_argument('--db-host', required=True,help="Database host")
  parser.add_argument('--db-database', required=True,help="Database host")
  parser.add_argument('--sw-user',required=True,help="Equipements user")
  parser.add_argument('--sw-pass',required=True,help="Equipements pass")
  parser.add_argument('--workers',default=5,help="Equipements pass")
  parser.add_argument('--binary',required=True,help="Equipements pass")
  parser.add_argument('--binary-md5',required=True,help="Equipements pass")
  parser.add_argument('--version',required=True,help="Equipements pass")
  parser.add_argument('--ftp-server',required=True,help="Equipements pass")
  parser.add_argument('--tftp-server',required=True,help="Equipements pass")
  parser.add_argument('--leases-file',required=True,help="Equipements pass")
  args = parser.parse_args()
  if not os.path.isfile(args.leases_file):
    sys.exit(1)
  args.leases=IscDhcpLeases(args.leases_file)
  return args

def ping(ip,timeout=2):
  try:
    s = socket.create_connection((ip, 22),timeout)
    s.close()
    return True
  except:
    pass
  return False

def checkMd5(net_connect,filename,md5):
  buf = net_connect.send_command('verify /md5 %s'%filename)
  if 'Error' in buf:
    net_connect.clear_buffer()
    return False
  print buf
  while "verify" not in buf:
    time.sleep(1)
    buf = net_connect.clear_buffer()
    print buf
  m=re.search('verify.*= ([0-9a-f]+)',buf)
  md5c = m.group(1)
  if md5 != md5c:
    return False
  return True

def checkVersion(net_connect,args):
  buf = net_connect.send_command('show ver')
  if args.version in buf:
    return True
  return False

def checkBinary(net_connect,args):
  buf = net_connect.send_command('dir')
  if ' ' + args.binary +"\n" in buf:
    return True
  return False

def download(net_connect,args):
  net_connect.send_command('copy ftp://anonymous@%s/%s flash:'%(args.ftp_server,args.binary))
  while '!' in buf:
    time.sleep(1)
    buf = net_connect.clear_buffer()
    print buf
  if not checkMd5(net_connect,args.binary,args.binary_md5):
    return False
  return True

def upgrade(net_connect,args):
  fbuf = u''
  buf = net_connect.send_command('soft install file flash:%s'%args.binary)
  print buf
  fbuf += buf
  while 'reload' not in fbuf:
    time.sleep(1)
    buf = net_connect.clear_buffer()
    if buf != None:
      print buf
      fbuf += buf
  if "Do you want to proceed with reload" in fbuf:
    buf = net_connect.send_command('yes')
    if buf != None:
      fbuf += buf
  if 'System configuration has been modified' in buf:
    buf = net_connect.send_command('no')
    if buf != None:
      fbuf += buf
  print fbuf
  time.sleep(10)
  
def copyConfig(net_connect,hostname,args):
  net_connect.send_command('switch %s priority 15'%number)
  net_connect.send_command('y')
  buf = net_connect.send_command('copy tftp://%s/fullconfig/%s-confg start'%(args.tftp_server,hostname))
  if 'OK' in buf:
    return True
  return False

def provision(net_connect,number):
  if number == 1:
    net_connect.send_command('switch %s priority 15'%number)
  elif number == 2:
    net_connect.send_command('switch %s priority 14'%number)
  else:
    net_connect.send_command('switch %s priority 1'%number)
  net_connect.send_command('y')
  buf = net_connect.send_command('show sw')
  m = re.search('\*(\d+)',buf)
  if m:
    id = m.group(1)
    if id!=number:
      buf = net_connect.send_command("sw %s ren %s"%(id,number))
      buf = net_connect.send_command("y")

def testEquipement(queue,args):
  myconn = mysql.connector.connect(host=args.db_host,user=args.db_user,password=args.db_pass,database=args.db_user)
  mycursor = myconn.cursor()
  while run:
    equipement = queue.get()
    device = {
      'device_type': 'cisco_ios',
      'ip': equipement['ip'],
      'username': args.sw_user,
      'password': args.sw_pass,
      'verbose':False,
    }
    try:
      if ping(equipement['ip']):
        logging.info("%s ping ok"%equipement['hostname'])
        mycursor.execute("update equipements set status=%s where id=%s",(1,equipement['id']))
        myconn.commit()
        net_connect = netmiko.ConnectHandler(**device)
        logging.info("%s connected"%equipement['hostname'])
        mycursor.execute("update equipements set status=%s where id=%s",(2,equipement['id']))
        myconn.commit()
        if not checkVersion(net_connect,args):
          logging.info("%s check version ko"%equipement['hostname'])
          if not checkBinary(net_connect,args):
            logging.info("%s check binary ko, downloading"%equipement['hostname'])
            mycursor.execute("update equipements set status=%s where id=%s",(5,equipement['id']))
            myconn.commit()
            download(net_connect,args)
          else:
            logging.info("%s check binary ok"%equipement['hostname'])
            mycursor.execute("update equipements set status=%s where id=%s",(4,equipement['id']))
            myconn.commit()
            if not checkMd5(net_connect,args.binary,args.binary_md5):
              logging.info("%s check md5 KO"%equipement['hostname'])
            else:
              logging.info("%s check md5 ok, upgrading"%equipement['hostname'])
              mycursor.execute("update equipements set status=%s where id=%s",(6,equipement['id']))
              myconn.commit()
              upgrade(net_connect,args)
        else:
          logging.info("%s check version ok"%equipement['hostname'])
          mycursor.execute("update equipements set status=%s where id=%s",(3,equipement['id']))
          myconn.commit()
          m=re.search('^slave(\d+)',equipement['template'])
          if m:
            id=m.group(1)
            provision(net_connect,id)
            logging.info("%s provision OK"%equipement['hostname'])
            mycursor.execute("update equipements set status=%s where id=%s",(9,equipement['id']))
          else:
            if not copyConfig(net_connect,equipement['hostname'],args):
              logging.info("%s copy KO"%equipement['hostname'])
              mycursor.execute("update equipements set status=%s where id=%s",(8,equipement['id']))
            else:
              logging.info("%s copy OK"%equipement['hostname'])
              mycursor.execute("update equipements set status=%s where id=%s",(9,equipement['id']))
          myconn.commit()
        net_connect.disconnect()
      else:
        logging.info("%s ping ko"%equipement['hostname'])
        mycursor.execute("update equipements set status=%s where id=%s",(0,equipement['id']))
    except Exception as e:
      print e
      pass
    myconn.commit()
    queue.task_done()


def loadList():
  myconn = mysql.connector.connect(host=args.db_host,user=args.db_user,password=args.db_pass,database=args.db_user)
  mycursor = myconn.cursor()
  mycursor.execute("select id, hostname, mac,template from equipements where status != 9")
  equipements=[]
  for eqptValues in mycursor.fetchall():
    equipement = {"id":eqptValues[0], "hostname":eqptValues[1], "mac":eqptValues[2],"template":eqptValues[3]}
    equipements.append(equipement)
  myconn.disconnect()
  return equipements

def clearQueue(queue):
  while not queue.empty():
    queue.get()
    queue.task_done()

def fillQueue(queue,args):
  while run:
    #logging.info('Fill queue')
    onlineEquipements=getOnlineEquipements(args)
    for equipement in onlineEquipements:
      queue.put(equipement)
    queue.join()
    time.sleep(1)

def getOnlineEquipements(args):
  equipements=loadList()
  leases=args.leases.get_current()
  online=[]
  for mac in leases:
    eq = [e for e in equipements if e['mac']==mac]
    if len(eq)>0:
      eq[0]['ip'] = leases[mac].ip
      online.append(eq[0])
  return online
  

logging.basicConfig(level=logging.INFO)
args = parse_args()
run = True
equipementList = Queue()
try:
  myconn = mysql.connector.connect(host=args.db_host,user=args.db_user,password=args.db_pass,database=args.db_user)
  mycursor = myconn.cursor()
except Exception as e:
  print "Can't connect to database",e
  sys.exit(1)

myconn.disconnect()
for i in xrange(args.workers):
  t = Thread(target=testEquipement,args=(equipementList,args,))
  t.daemon = True
  t.start()

t = Thread(target=fillQueue,args=(equipementList,args,))
t.daemon = True
t.start()

while run:
  t.join(1)
  if not t.isAlive():
    break

