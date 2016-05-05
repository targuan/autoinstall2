#!/usr/bin/python
from threading import Thread, RLock
import signal
import os
import socket
import time
import sys
import argparse
import netmiko
import re
import urllib2
import urllib
import json
import random
from tftp import *
import logging
from isc_dhcp_leases.iscdhcpleases import Lease, IscDhcpLeases


from client import ClientIOSXE


def stop_handler(signum, frame):
    global run
    logger.info('Shutting down')
    run = False


def parse_args():
    parser = argparse.ArgumentParser(description="", fromfile_prefix_chars='@')
    parser.add_argument('--sw-user', required=True, help="Equipements user")
    parser.add_argument('--sw-pass', required=True, help="Equipements pass")
    parser.add_argument('--workers', default=5,
                        help="Size of ssh client pool", type=int)
    parser.add_argument('--ftp-server', required=True,
                        help="FTP server to download the binary",
                        action="append")
    parser.add_argument('--tftp-server', required=True,
                        help="TFTP server to download the configuration")
    parser.add_argument('--leases-file', required=True,
                        help="ISC DHCPD lease file location")
    parser.add_argument('--debug', help="Debug level",
                        type=int, choices=[0, 1, 2], default=0)
    parser.add_argument('--http-root', required=True,
                        help='Web server address')
    args = parser.parse_args()

    if not os.path.isfile(args.leases_file):
        sys.exit(1)
    args.leases = IscDhcpLeases(args.leases_file)

    levels = [logging.WARNING, logging.INFO, logging.DEBUG]
    logger.setLevel(levels[args.debug])

    return args


def update_status(args, status, id):
    url = '%s/equipements/updateStatus/%s/%s.json' % (args.http_root, id,
                                                      urllib.quote(status))
    fp = urllib2.urlopen(url)
    result = fp.read()


class InstallEquipement:
    def __init__(self, equipement, args, queue):
        self.equipement = equipement
        self.args = args
        self.conn = ClientIOSXE(equipement['ip'], args.sw_user, args.sw_pass)
        self.queue = queue

    def run(self):
        update_status(args, 'leased', self.equipement['id'])
        logger.debug("Running %s" % self.equipement['name'])
        return self.ping

    def ping(self):
        update_status(args, 'pinging', self.equipement['id'])
        logger.debug("Pinging %s" % self.equipement['name'])
        if self.conn.ping():
            update_status(args, 'ping ok', self.equipement['id'])
            return self.connect
        else:
            update_status(args, 'ping ko', self.equipement['id'])
            return self.disconnect

    def connect(self):
        update_status(args, 'connecting', self.equipement['id'])
        logger.debug("Connectinging %s" % self.equipement['name'])
        try:
            self.conn.connect()
            update_status(args, 'Connection ok', self.equipement['id'])
            return self.check_version
        except:
            update_status(args, 'Connection ko', self.equipement['id'])
            return self.disconnect

    def check_version(self):
        update_status(args, 'Checking version', self.equipement['id'])
        logger.debug("Verifying version of %s" % self.equipement['name'])
        if self.conn.check_version(self.equipement['version']):
            update_status(args, 'version ok', self.equipement['id'])
            return self.provision
        else:
            update_status(args, 'version ko', self.equipement['id'])
            return self.file_exists

    def file_exists(self):
        update_status(args, 'verifying if binary exists',
                      self.equipement['id'])
        logger.debug("Verifying if binary is on %s" % self.equipement['name'])
        if self.conn.file_exists(self.equipement['binary']):
            update_status(args, 'binary found', self.equipement['id'])
            return self.upgrade
        else:
            update_status(args, 'binary not found', self.equipement['id'])
            return self.download

    def download(self):
        update_status(args, 'downloading binary', self.equipement['id'])
        logger.debug("Downloading binary on %s" % self.equipement['name'])
        path = 'ftp://%s/%s' % (random.choice(self.args.ftp_server),
                                self.equipement['binary'])
        if self.conn.download(path):
            update_status(args, 'download ok', self.equipement['id'])
            return self.upgrade
        else:
            update_status(args, 'download ko', self.equipement['id'])
            return self.disconnect

    def upgrade(self):
        update_status(args, 'upgrading', self.equipement['id'])
        logger.debug("Upgrading %s" % self.equipement['name'])
        if self.conn.upgrade(self.equipement['binary']):
            update_status(args, 'upgrade ok', self.equipement['id'])
            return self.provision
        else:
            update_status(args, 'upgrade ko', self.equipement['id'])
            return self.error

    def provision(self):
        update_status(args, 'provisionning', self.equipement['id'])
        logger.debug("Provisionning %s" % self.equipement['name'])
        m = re.search('^slave(\d+)', self.equipement['template'])
        if m:
            id = m.group(1)
            self.conn.provision(id)
            return self.finished
        else:
            self.conn.provision(1)
            return self.install

    def install(self):
        update_status(args, 'installing configuration', self.equipement['id'])
        logger.debug("Installing %s" % self.equipement['name'])
        if self.conn.copy_config(self.equipement['name'],
                                 self.args.tftp_server):
            update_status(args, 'installation ok', self.equipement['id'])
            return self.finished
        else:
            update_status(args, 'installation error', self.equipement['id'])
            return self.error

    def finished(self):
        update_status(args, 'completed', self.equipement['id'])
        logger.debug("Finishing %s" % self.equipement['name'])
        return self.disconnect

    def error(self):
        update_status(args, 'error', self.equipement['id'])
        logger.debug("Fatal error %s" % self.equipement['name'])
        return self.disconnect

    def disconnect(self):
        self.conn.disconnect()
        return None


def test_equipement(queue, args):
    while run:
        try:
            equipement = None

            with equipement_lock:
                macs = [mac for mac in queue if not queue[mac]['running']]
                if len(macs) > 0:
                    equipement = queue[macs[0]]
                    equipement['running'] = True

            if equipement is None:
                time.sleep(1)
                continue

            method = InstallEquipement(equipement, args, queue).run()
            while method is not None:
                method = method()
            with equipement_lock:
                del queue[equipement['mac']]
        except Exception as e:
            if equipement:
                with equipement_lock:
                    equipement['running'] = False
            logger.warning(e)


def load_list(args):
    url = '%s/equipements/index.json' % args.http_root
    fp = urllib2.urlopen(url)
    full_inventory = json.loads(fp.read())
    filtered = {e['mac']: e for e in full_inventory
                if e['status'] != 'completed'}
    return filtered


if __name__ == '__main__':
    logger = logging.getLogger('autoinstall.main')
    logger.setLevel(logging.DEBUG)
    ch = logging.StreamHandler()
    ch.setLevel(logging.DEBUG)
    formatter = logging.Formatter(
        '%(asctime)s - %(name)s - %(filename)s '
        '%(lineno)d - %(thread)d - %(levelname)s - %(message)s')
    ch.setFormatter(formatter)
    logger.addHandler(ch)
    logger.propagate = False
    logger.info('Starting')

    args = parse_args()
    run = True
    equipementList = {}
    equipement_lock = RLock()

    logger.debug('Installing SIGINT handler')
    signal.signal(signal.SIGINT, stop_handler)

    logger.debug('Starting %d workers' % args.workers)
    for i in xrange(args.workers):
        t = Thread(target=test_equipement, args=(equipementList, args, ))
        t.start()

    logger.debug('Starting tftp server')
    server = TFTPServer(root='/srv/tftp', leases=args.leases,
                        httproot=args.http_root)
    server.start()

    logger.debug('Running')
    leasesList = set()
    while run:
        leases = args.leases.get_current()
        logger.debug('Found %d active leases' % len(leases))
        equipements = load_list(args)
        with equipement_lock:
            for mac in leases:
                if mac in equipements and mac not in equipementList:
                    equipements[mac]['ip'] = leases[mac].ip
                    equipements[mac]['running'] = False
                    equipementList[mac] = equipements[mac]

        time.sleep(1)
