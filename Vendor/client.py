import netmiko
import socket
import re
import time
import uuid
import logging


logger = logging.getLogger('autoinstall.client')
logger.setLevel(logging.DEBUG)
logger.setLevel(logging.DEBUG)
ch = logging.StreamHandler()
ch.setLevel(logging.DEBUG)
formatter = logging.Formatter(
    '%(asctime)s - %(name)s - %(filename)s %(lineno)d - ' +
    '%(thread)d - %(levelname)s - %(message)s')
ch.setFormatter(formatter)
logger.addHandler(ch)
logger.propagate = False


class Client:

    def __init__(self, host, username, password, archive_file = None):
        self.host = host
        self.username = username
        self.password = password
        if archive_file is None:
            archive_file = '/tmp/%s-%s' % (host, uuid.uuid1())
        self.archive = open(archive_file, 'a')
        logger.debug('archiving to %s', archive_file)
        self.net_connect = None

    def send_command(self, command_string, delay_factor=.1, max_loops=150,
                     strip_prompt=True, strip_command=True):
        self.archive.write(command_string)
        ret = self.net_connect.send_command(
            command_string, delay_factor, max_loops, False, False)
        if ret is not None:
            self.archive.write(ret)
            self.archive.flush()
        if strip_command:
            ret = self.net_connect.strip_command(command_string, ret)
        if strip_prompt:
            ret = self.net_connect.strip_prompt(ret)
        return ret

    def clear_buffer(self):
        ret = self.net_connect.clear_buffer()
        if ret is not None:
            self.archive.write(ret)
            self.archive.flush()
        return ret

    def connect(self,):
        device = {
            'device_type': 'cisco_ios',
            'ip': self.host,
            'username': self.username,
            'password': self.password,
            'verbose': False,
        }
        self.net_connect = netmiko.ConnectHandler(**device)

    def check_version(self, fingerprint):
        buf = self.send_command('show version')
        if fingerprint in buf:
            return True
        return False

    def ping(self, timeout=2):
        try:
            s = socket.create_connection((self.host, 22), timeout)
            s.close()
            return True
        except Exception as e:
            pass
        return False

    def md5sum(self, filename, md5):
        buf = self.send_command('verify /md5 %s' % filename)
        if 'Error' in buf:
            self.clear_buffer()
            return False
        while "verify" not in buf:
            time.sleep(1)
            buf = self.clear_buffer()
        m = re.search('verify.*= ([0-9a-f]+)', buf)
        md5c = m.group(1)
        if md5 != md5c:
            return False
        return True

    def file_exists(self, filename):
        buf = self.send_command('dir')
        if ' ' + filename + "\n" in buf:
            return True
        return False

    def download(self, filename):
        buf = self.send_command('copy %s flash:' % (filename))
        if buf is None:
            fbuf = ''
        else:
            fbuf = buf
        while 'Error' not in fbuf and 'OK' not in fbuf:
            time.sleep(0.1)
            buf = self.clear_buffer()
            if buf is not None:
                fbuf += buf
        if 'Error' in fbuf:
            return False
        if 'OK' in buf:
            return True
        return False

    def upgrade(self, filename):
        raise NotImplementedError

    def copy_config(self, name, tftp_server):
        fbuf = ''
        cmd = 'copy tftp://%s/%s-confg startup-config' % (tftp_server, name)
        buf = self.send_command(cmd,
                                strip_command=False,
                                strip_prompt=False,
                                delay_factor=5)
        fbuf = buf if buf is not None else ''
        if 'Destination filename' in buf:
            buf = self.send_command('')
            fbuf += buf if buf is not None else ''
        while 'Error' not in fbuf and 'OK' not in fbuf:
            time.sleep(1)
            buf = self.clear_buffer()
            fbuf += buf if buf is not None else ''
        if 'OK' in fbuf:
            return True
        return False

    def provision(self, number):
        if number == 1:
            self.send_command('switch %s priority 15' % number)
        elif number == 2:
            self.send_command('switch %s priority 14' % number)
        else:
            self.send_command('switch %s priority 1' % number)
        self.send_command('y')
        buf = self.send_command('show switch')
        m = re.search('\*(\d+)', buf)
        if m:
            id = m.group(1)
            if id != number:
                buf = self.send_command("switch %s renumber %s" % (id, number))
                buf = self.send_command("y")

    def disconnect(self):
        if self.net_connect is not None:
            self.net_connect.disconnect()

    def shut(self):
        self.net_connect.send_config_set(["interface range g1/0/1-24", "shut"])


class ClientIOSXE(Client):

    def upgrade(self, filename):
        fbuf = u''
        buf = self.send_command('software install file flash:%s' % filename)
        fbuf += buf
        while 'reload' not in fbuf:
            time.sleep(1)
            buf = self.clear_buffer()
            if buf is not None:
                fbuf += buf
            if '%' in fbuf:
                return False
        if "Do you want to proceed with reload" in fbuf:
            buf = self.send_command('no')
            if buf is not None:
                fbuf += buf
        if 'System configuration has been modified' in buf:
            buf = self.send_command('no')
            if buf is not None:
                fbuf += buf
        return True
