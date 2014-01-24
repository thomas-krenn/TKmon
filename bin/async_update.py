#!/usr/bin/env python
#
# This file is part of TKMON
#
# TKMON is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# TKMON is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with TKMON.  If not, see <http://www.gnu.org/licenses/>.
#
# @author Marius Hein <marius.hein@netways.de>
# @copyright 2012-2014 NETWAYS GmbH <info@netways.de>
#

from __future__ import division
import sys
import os
import fcntl
import logging
import subprocess
import time
import re

APTGET_BIN  = '/usr/bin/apt-get'
STATUS_FILE = '/tmp/async_update.status'
LOCK_FILE   = '/tmp/async_update.lock'
INFO_FILE   = '/tmp/async_update.info'
ERROR_FILE  = '/tmp/async_update.err'

RE_SUM      = re.compile('^(\d+) upgraded, (\d+) newly installed', re.I)
RE_ITEM     = re.compile('^setting up ([^\s]+)', re.I)

def update_status(starttime, percentage, running, error):
    runtime=time.time() - starttime
    with open(STATUS_FILE, 'w') as f:
        data = (time.time(), runtime, percentage, running, error)
        f.write(' '.join(map(str, data)))
        f.close()

def write_line(file, line):
    with open(file, 'a') as f:
        f.write(line)
        f.close

def truncate_file(file):
    with open(file, 'w') as f:
        f.close()

def main():
    logging.getLogger().setLevel(logging.DEBUG)
    log = logging.getLogger(__name__)
    
    log.debug('Starting up')
    
    starttime=time.time()
    
    proc = subprocess.Popen([
        APTGET_BIN + ' -o Dpkg::Options::=--force-confold'
        + ' -y dist-upgrade'
        ], env={
            'DEBIAN_FRONTEND': 'noninteractive',
            'PATH' : '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/games'
        },
        shell=True,
        stdin=subprocess.PIPE,
        stdout=subprocess.PIPE,
        stderr=subprocess.PIPE
        )
    
    percentage      = 0;
    workItems       = 0;
    currentItems    = 0;
    currentOld      = 0;
    
    truncate_file(INFO_FILE)
    truncate_file(ERROR_FILE)
    
    update_status(starttime, percentage, True, False)
    
    while proc.returncode is None:
        proc.poll()

        for line in iter(proc.stdout.readline, b''):
            if workItems == 0:
                itemMatch = re.match(RE_SUM, line);
                if itemMatch:
                    workItems = int(itemMatch.group(1)) + int(itemMatch.group(2))
                    log.info('Found %d work items', workItems)
            else:
                itemMatch = re.match(RE_ITEM, line);
                if itemMatch:
                    currentItems += 1
                percentage = float(currentItems / workItems) * 100
                
            if workItems and currentOld != currentItems:
                log.info('Upgrade %d of %d items (%.2f%%)', currentItems, workItems, percentage)
                update_status(starttime, percentage, True, True)
                currentOld = currentItems
            
            write_line(INFO_FILE, line)
        
        for line in iter(proc.stderr.readline, b''):
            write_line(ERROR_FILE, line)
        
        time.sleep(0.2)
    
    error = False
    if proc.returncode != 0:
        log.error('Error occurred, please check ' + ERROR_FILE)
        error = True
    
    update_status(starttime, 100, False, error)
    log.info('Update finish, needed %.2f seconds', time.time() - starttime)
    

class LogFormatter(logging.Formatter):
    """Log formatter with color support used in inGraph.
    This formatter is enabled automatically by importing `~ingraph.log`."""
    def __init__(self, *args, **kwargs):
        logging.Formatter.__init__(self, *args, **kwargs)
        self._color = sys.stderr.isatty()
        if self._color:
            self._colors = {
                logging.DEBUG: ('\x1b[34m',),  # Blue
                logging.INFO: ('\x1b[32m',),  # Green
                logging.WARNING: ('\x1b[33m',),  # Yellow
                logging.ERROR: ('\x1b[31m',),  # Red
                logging.CRITICAL: ('\x1b[1m', '\x1b[31m'),  # Bold, Red
            }
            self._footer = '\x1b[0m'

    def format(self, record):
        formatted_message = logging.Formatter.format(self, record)
        if self._color:
            formatted_message = (''.join(self._colors.get(record.levelno)) +
                                 formatted_message +
                                 len(self._colors.get(record.levelno)) * self._footer)
        return formatted_message

class LockFile(object):
    def __init__(self, lockfile):
        self.lockfile = lockfile
    def __enter__(self):
        self.fh = open(self.lockfile, 'w')
        fcntl.flock(self.fh, fcntl.LOCK_EX | fcntl.LOCK_NB)
    def __exit__(self, type, value, traceback):
        fcntl.flock(self.fh, fcntl.LOCK_UN)
        self.fh.close()
        try:
            os.remove(self.lockfile)
        except e:
            pass

if __name__ == '__main__':
    _CHANNEL = logging.StreamHandler()
    _CHANNEL.setFormatter(LogFormatter(fmt='%(asctime)s [%(levelname)s] %(message)s'))
    logging.getLogger().addHandler(_CHANNEL)
    try:
        with LockFile(LOCK_FILE):
            sys.exit(main())
    except IOError, e:
        sys.stderr.write('Script is already running\n')
        sys.exit(1)
