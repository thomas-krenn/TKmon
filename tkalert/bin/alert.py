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
    module:: alert
"""

import sys
reload(sys)
sys.setdefaultencoding("utf-8")

import time
import os.path

from datetime import datetime

import tkalert
import logging
from tkalert.options import MyOptions, MyOptionIsMandatoryError
from tkalert.data import HeartbeatObject, AlertObject, \
    map_alert_object_to_arguments
from tkalert.settings import MAIL_SERVER, MAIL_TARGET_ADDRESS, GNUPG_KEY, VERSION_STRING
from tkalert.mail import Mailer
from tkalert.gnupg import GnupgCommand, GnupgCommandException


def main():
    """Main script to trigger alerting
        Returns:
            int. The return code
    """

    time_start = time.time()

    myoptions = MyOptions(usage="%prog --type=<heartbeat|service|test> [--help]",
                          version="%prog " + tkalert.__version__)

    try:
        (options, args) = myoptions.parse_args()

        if options.verbose is True:
            logging.getLogger().setLevel(logging.DEBUG)

        log = logging.getLogger(__name__)
        log.debug('Starting up')

        log.debug('Testing gnupg environment')
        gnupg_paths = [
            options.gnupgconfig,
            '/etc/tkalert/gnupg.conf',
            '/usr/local/tkalert/etc/gnupg.conf'
        ]

        for test_path in gnupg_paths:
            if test_path and os.path.exists(test_path):
                log.debug('setting --gnupg-config=' + test_path)
                options.gnupgconfig = test_path
                break

        xml_object = None

        if options.type == "heartbeat" or options.type == "test" :
            log.info('Creating heartbeat object')
            xml_object = HeartbeatObject()
        elif options.type == "service":
            log.info('Creating alert object')
            xml_object = AlertObject()

        xml_object.set_authkey(options.auth)
        xml_object.set_contact_email(options.mail)
        xml_object.set_contact_name(options.person)

        if options.date is None:
            log.info('Set --date switch to NOW')
            xml_object.set_date_to_now()
        else:
            now = datetime.fromtimestamp(int(options.date))
            log.info('Switch --date was set to "%s"', now)
            xml_object.set_date(now)

        if options.type == "service":
            map_alert_object_to_arguments(options, xml_object)

        if options.dumpxml is not None:
            log.debug('Dump xml to file (%s)', options.dumpxml)
            with open(options.dumpxml, 'w') as file_handle:
                file_handle.write(str(xml_object))
            return 0

        if options.noenc is True:
            data = xml_object.toxml()
        else:
            gpg = GnupgCommand(options.gnupgconfig)
            try:
                data = gpg.crypt_ascii(GNUPG_KEY, xml_object.toxml())
            except GnupgCommandException as e:
                log.error(e.message)
                return 255

        mailer = Mailer()
        mailer.server = MAIL_SERVER

        if options.targetmail is not None:
            log.debug('Override target mail address: %s', options.targetmail)
            mailer.receiver = options.targetmail
        else:
            mailer.receiver = MAIL_TARGET_ADDRESS

        if options.type == "test":
            mailer.test_mode = True

        mailer.sender = options.mail
        mailer.sender_name = options.person
        mailer.alert_type = options.type
        mailer.content = data
        mailer.send()

        time_run = time.time() - time_start

        log.info('Runtime %.4f seconds', time_run)

        if options.checkplugin is True:
            output = "%s sent %s - OK|runtime=%.4fs" % (VERSION_STRING, options.type, time_run, )
            print (output)

    except MyOptionIsMandatoryError as mandatory_error:
        print(mandatory_error.message + "\n")
        myoptions.print_usage()
        return 255
    return 0

if __name__ == "__main__":
    sys.exit(main())