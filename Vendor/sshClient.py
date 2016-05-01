#!/usr/bin/python
import Queue
from threading import Thread
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


def push(queue, equipement, args):
    if equipement['mac'] in args.leases.get_current():
        logger.debug("Pushing back %s" % equipement['name'])
        queue.put(equipement)


def test_equipement(queue, args):
    while run:
        try:
            equipement = queue.get(True, 1)
            update_status(args, 'leased', equipement['id'])
            conn = ClientIOSXE(equipement['ip'], args.sw_user, args.sw_pass)
            logger.debug('%s Running' % equipement['name'])
            if not conn.ping():
                logger.debug('%s Ping KO' % equipement['name'])
                push(queue, equipement, args)
                update_status(args, 'ping ko', equipement['id'])
                continue

            logger.debug('%s Ping OK' % equipement['name'])
            update_status(args, 'ping ok', equipement['id'])

            try:
                update_status(args, 'connecting', equipement['id'])
                conn.connect()
                update_status(args, 'connecting ok', equipement['id'])
            except:
                logger.debug('%s Connection KO' % equipement['name'])
                update_status(args, 'connecting ko', equipement['id'])
                push(queue, equipement, args)
                continue

            logger.info("%s Connection OK" % equipement['name'])

            update_status(args, 'checking version', equipement['id'])
            if not conn.check_version(equipement['version']):
                update_status(args, 'version ko', equipement['id'])
                logger.info("%s version KO %s" % (equipement['name'],
                                                  equipement['version']))

                update_status(args, 'checking for binary file',
                              equipement['id'])
                if not conn.file_exists(equipement['binary']):
                    logger.info("%s Binary file not found" %
                                equipement['name'])
                    path = 'ftp://%s/%s' % (random.choice(args.ftp_server),
                                            equipement['binary'])
                    update_status(args, 'downloading binary', equipement['id'])
                    if not conn.download(path):
                        update_status(args, 'download failed',
                                      equipement['id'])
                        logger.debug('%s Binary download failed' %
                                     equipement['name'])
                        push(queue, equipement, args)
                        conn.disconnect()
                        continue
                    else:
                        update_status(args, 'download ok', equipement['id'])
                        logger.debug('%s Binary download OK' %
                                     equipement['name'])

                logger.info("%s Binary found" % equipement['name'])
                update_status(args, 'installing', equipement['id'])
                conn.upgrade(equipement['binary'])
            else:
                logger.info("%s version OK" % equipement['name'])
                update_status(args, 'version ok', equipement['id'])
                m = re.search('^slave(\d+)', equipement['template'])
                if m:
                    id = m.group(1)
                    update_status(args, 'provisionning slave',
                                  equipement['id'])
                    conn.provision(id)
                    logger.info("%s provision OK" % equipement['name'])
                    update_status(args, 'completed', equipement['id'])
                    conn.disconnect()
                else:
                    update_status(args, 'provisionning', equipement['id'])
                    conn.provision(1)
                    update_status(args, 'copying conf', equipement['id'])
                    if not conn.copy_config(equipement['name'],
                                            args.tftp_server):
                        update_status(args, 'copy conf error',
                                      equipement['id'])
                        logger.info("%s copy KO" % equipement['name'])
                        push(queue, equipement, args)
                        conn.disconnect()
                    else:
                        update_status(args, 'completed', equipement['id'])
                        logger.info("%s copy OK" % equipement['name'])
                        conn.disconnect()
                        continue
        except Queue.Empty:
            continue
        except Exception as e:
            print e
        queue.task_done()


def load_list(args):
    url = '%s/equipements/index.json' % args.http_root
    fp = urllib2.urlopen(url)
    full_inventory = json.loads(fp.read())
    filtered = [e for e in full_inventory if e['status'] != 'completed']
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
    equipementList = Queue.Queue()

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
        currentLeasesList = set([(mac, leases[mac].ip) for mac in leases])
        newLeases = currentLeasesList - leasesList
        leasesList = currentLeasesList
        logger.debug('%d New leases' % len(newLeases))

        if len(newLeases) > 0:
            equipements = load_list(args)
            for lease in newLeases:
                eq = [e for e in equipements if e['mac'] == lease[0]]
                if len(eq) > 0:
                    eq[0]['ip'] = lease[1]
                    logger.debug('Add %s to process list' % str(eq[0]))
                    equipementList.put(eq[0])
                else:
                    logger.debug(
                        'Lease for mac %s is not in the database or '
                        'in finished state' % lease[0])

        time.sleep(1)
