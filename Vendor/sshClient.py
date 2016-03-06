#!/usr/bin/python
from mysql.connector import connect
from Queue import Queue
from threading import Thread
import signal, os, socket
import time, sys

import netmiko

run = True

def stop_handler(signum,frame):
  global run
  run = False

signal.signal(signal.SIGINT,stop_handler)

equipementList = Queue()
logQueue = Queue()

myconn = connect(host="localhost",user="autoinstall",database="autoinstall")
mycursor = myconn.cursor()

def ping(ip,timeout=2):
  try:
    s = socket.create_connection((ip, 22),timeout)
    s.close()
    return True
  except:
    pass
  return False

def writemem(ip):
  try:
    device = {
      'device_type': 'cisco_ios',
      'ip':   ip,
      'username': '',
      'password': '',
      'secret': '',
      'verbose': True,
    }
    net_connect = netmiko.ConnectHandler(**device)
    net_connect.enable()
    output = net_connect.send_command('wr mem')
    print output
    net_connect.disconnect()
    return True
  except Exception as e:
    print e
  return False

def testEquipement(queue):
  while run:
    equipement = queue.get()
    if ping(equipement['ip']):
      logQueue.put("%s ping ok"%equipement['hostname'])
      if writemem(equipement['ip']):
        logQueue.put('%s wr mem OK'%equipement['hostname'])
      else:
        logQueue.put('%s wr mem KO'%equipement['hostname'])
        queue.put(equipement)
    else:
      logQueue.put("%s ping ko"%equipement['hostname'])
      queue.put(equipement)
    queue.task_done()

def loadList(queue):
  mycursor.execute("select id, hostname, ip from equipements")
  for eqptValues in mycursor.fetchall():
    equipement = {"id":eqptValues[0], "hostname":eqptValues[1], "ip":eqptValues[2]}
    queue.put(equipement)

def clearQueue(queue):
  while not queue.empty():
    queue.get()
    queue.task_done()

def waitTest(queue):
  queue.join()

def logWorker(queue):
  while True:
    log = queue.get()
    print log
    queue.task_done()

for i in xrange(10):
  t = Thread(target=testEquipement,args=(equipementList,))
  t.daemon = True
  t.start()

t = Thread(target=logWorker,args=(logQueue,))
t.daemon = True
t.start()

t = Thread(target=waitTest,args=(equipementList,))
t.daemon = True
loadList(equipementList)
t.start()

while run:
  t.join(1)
  if not t.isAlive():
    break

