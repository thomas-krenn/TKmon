# Copyright (C) 2013 NETWAYS GmbH, http://netways.de
#
# This file is part of TKALERT (http://www.thomas-krenn.com/).
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <http://www.gnu.org/licenses/>.

"""
    module:: gnupg
"""

import os
import logging
from subprocess import Popen, PIPE

__ALL__ = ['GnupgCommand']

GPG = '/usr/bin/gpg'

LOG = logging.getLogger(__name__)


class GnupgCommand(object):
    """Process interface to gnupg"""

    def __init__(self, config):
        self.config = config
        self.home_dir = os.path.dirname(self.config)

        self._args = [GPG, '--home', self.home_dir,
                      '--options', self.config,
                      '--batch']

    def crypt_ascii(self, keyid, data):
        """Crypt text to ascii output

        Args:
            keyid (string) index of gpg key
            data (string) data to encrypt

        Returns:
            encrypted data (string)
        """
        args = self._args[:]
        args.append('--encrypt')
        args.append('-a')
        args.append('-r')
        args.append(keyid)

        LOG.debug('Call GPG: %s', ' '.join(args))

        proc = Popen(args, stdin=PIPE, stdout=PIPE, stderr=PIPE)
        (output, errdata) = proc.communicate(input=data)

        if (errdata):
            raise GnupgCommandException(errdata)

        return output

class GnupgCommandException(Exception):
    """Gnupg error"""
    pass
