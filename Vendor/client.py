import netmiko,socket,re,time,uuid,logging


logger = logging.getLogger('autoinstall.client')
logger.setLevel(logging.DEBUG)
logger.setLevel(logging.DEBUG)
ch = logging.StreamHandler()
ch.setLevel(logging.DEBUG)
formatter = logging.Formatter('%(asctime)s - %(name)s - %(filename)s %(lineno)d - %(thread)d - %(levelname)s - %(message)s')
ch.setFormatter(formatter)
logger.addHandler(ch)
logger.propagate = False

class Client:
  def __init__(self,host,username,password):
    self.host=host
    self.username=username
    self.password=password
    archivename='/tmp/%s-%s'%(host,uuid.uuid1())
    self.archive=open(archivename,'w')
    logger.debug('archiving to %s',archivename)
  
  def send_command(self, command_string, delay_factor=.1, max_loops=150,
                     strip_prompt=True, strip_command=True):
    self.archive.write(command_string)
    ret=self.net_connect.send_command(command_string, delay_factor, max_loops,False, False)
    if ret != None:
      self.archive.write(ret)
      self.archive.flush()
    if strip_command:
      ret = self.net_connect.strip_command(command_string, ret)
    if strip_prompt:
      ret = self.net_connect.strip_prompt(ret)
    return ret
  
  def clear_buffer(self):
    ret=self.net_connect.clear_buffer()
    if ret != None:
      self.archive.write(ret)
      self.archive.flush()
    return ret
  
  def connect(self,):
    device = {
      'device_type': 'cisco_ios',
      'ip': self.host,
      'username': self.username,
      'password': self.password,
      'verbose':False,
    }
    self.net_connect = netmiko.ConnectHandler(**device)
  
  def checkVersion(self,fingerprint):
    buf = self.send_command('show ver')
    if fingerprint in buf:
      return True
    return False
  
  def ping(self,timeout=2):
    try:
      s = socket.create_connection((self.host, 22),timeout)
      s.close()
      return True
    except Exception as e:
      pass
    return False
  
  def md5sum(self,filename,md5):
    buf = self.send_command('verify /md5 %s'%filename)
    if 'Error' in buf:
      self.clear_buffer()
      return False
    while "verify" not in buf:
      time.sleep(1)
      buf = self.clear_buffer()
    m=re.search('verify.*= ([0-9a-f]+)',buf)
    md5c = m.group(1)
    if md5 != md5c:
      return False
    return True
  
  def fileExists(self,filename):
    buf = self.send_command('dir')
    if ' ' + filename +"\n" in buf:
      return True
    return False
  
  def download(self,filename):
    buf=self.send_command('copy %s flash:'%(filename))
    while buf == None:
      buf = self.clear_buffer()
    if 'Error' in buf:
      return False
    while buf == None or '!' in buf:
      time.sleep(1)
      buf = self.clear_buffer()
    if 'OK' in buf:
      return True
    return False
  
  def upgrade(self,filename):
    fbuf = u''
    buf = self.send_command('soft install file flash:%s'%filename)
    fbuf += buf
    while 'reload' not in fbuf:
      time.sleep(1)
      buf = self.clear_buffer()
      if buf != None:
        fbuf += buf
    if "Do you want to proceed with reload" in fbuf:
      buf = self.send_command('yes')
      if buf != None:
        fbuf += buf
    if 'System configuration has been modified' in buf:
      buf = self.send_command('no')
      if buf != None:
        fbuf += buf
    time.sleep(10)
  
  def copyConfig(self,hostname,tftp_server):
    buf = self.send_command('copy tftp://%s/%s-confg startup-config'%(tftp_server,hostname),strip_command=False,strip_prompt=False,delay_factor=5)
    if 'OK' in buf:
      return True
    return False
  
  def provision(self,number):
    if number == 1:
      self.send_command('switch %s priority 15'%number)
    elif number == 2:
      self.send_command('switch %s priority 14'%number)
    else:
      self.send_command('switch %s priority 1'%number)
    self.send_command('y')
    buf = self.send_command('show sw')
    m = re.search('\*(\d+)',buf)
    if m:
      id = m.group(1)
      if id!=number:
        buf = self.send_command("sw %s ren %s"%(id,number))
        buf = self.send_command("y")
  
  def disconnect(self):
    self.net_connect.disconnect()

class ClientIOSXE(Client):
  def upgrade(self,filename):
    fbuf = u''
    buf = self.send_command('soft install file flash:%s'%filename)
    fbuf += buf
    while 'reload' not in fbuf:
      time.sleep(1)
      buf = self.clear_buffer()
      if buf != None:
        fbuf += buf
    if "Do you want to proceed with reload" in fbuf:
      buf = self.send_command('yes')
      if buf != None:
        fbuf += buf
    if 'System configuration has been modified' in buf:
      buf = self.send_command('no')
      if buf != None:
        fbuf += buf
    time.sleep(10)
