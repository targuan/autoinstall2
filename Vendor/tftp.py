import tftpy
import logging
import threading
import isc_dhcp_leases.iscdhcpleases
import cStringIO
import urllib2
import re

logger = logging.getLogger('autoinstall.tftp')
logger.setLevel(logging.DEBUG)
logger.setLevel(logging.DEBUG)
ch = logging.StreamHandler()
ch.setLevel(logging.DEBUG)
formatter = logging.Formatter(
    '%(asctime)s - %(name)s - %(filename)s %(lineno)d - %(thread)d -'
    ' %(levelname)s - %(message)s')
ch.setFormatter(formatter)
logger.addHandler(ch)
logger.propagate = False


logging.getLogger('tftpy').setLevel(logging.CRITICAL)


class TFTPServer:

    def __init__(self, address="0.0.0.0", port=69, root="/dev/null",
                 leases=None, httproot=''):
        self.address = address
        self.port = port
        self.root = root
        self.running = False
        self.leases = leases
        self.httproot = httproot

    def _get(self, filename):
        file = ''
        logger.info('Serving %s' % filename)
        if filename == 'network-confg':
            leases = self.leases.get_current()
            i = 0
            for mac in leases:
                file += "ip host boot%i %s\n" % (i, leases[mac].ip)
                i += 1
        elif re.match('(dhcp.*|router|boot\d*)-confg',filename):
            try:
                fp = urllib2.urlopen(
                    '%s/parameters/get/boottemplate' % self.httproot)
                file = fp.read()
                fp.close()
            except:
                logger.error("Can't get boottemplate from the webserver")
                pass
        elif '-confg' in filename:
            name = filename[:-6]
            logger.info("Getting %s from the webserver" % (name))
            try:
                logger.debug('%s/equipements/getByName/%s' %
                             (self.httproot, name))
                fp = urllib2.urlopen(
                    '%s/equipements/getByName/%s' % (self.httproot, name))
                file = fp.read()
                fp.close()
            except:
                logger.error(
                    "Can't get the configuration %s from the webserver" % name)
                pass
        return cStringIO.StringIO(file)

    def _run(self):
        try:
            self.server = tftpy.TftpServer(self.root, self._get)
            self.running = True
            self.server.listen(self.address, self.port)
            self.running = False
        except Exception as e:
            logger.error("Can't start TFTP server %s", e)
            self.running = False

    def start(self):
        logger.info("Starting TFTP server %s:%s" % (self.address, self.port))
        t = threading.Thread(None, self._run)
        t.daemon = True
        t.start()

    def stop(self):
        self.server.stop()
