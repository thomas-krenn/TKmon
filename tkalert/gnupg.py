"""
    module:: gnupg
"""

import os
import logging
from subprocess import Popen, PIPE

__ALL__ = ['GnupgInterface']

GPG = '/usr/bin/gpg'

log = logging.getLogger(__name__)


class GnupgInterface(object):
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

        log.debug('Call GPG: %s', ' '.join(args))

        proc = Popen(args, stdin=PIPE, stdout=PIPE, stderr=PIPE)
        (output, errdata) = proc.communicate(input=data)

        if (errdata):
            log.error(errdata)

        return output
