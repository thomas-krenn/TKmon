import sys

from datetime import datetime

import tkalert
import logging
from tkalert.options import MyOptions, MyOptionIsMandatoryError
from tkalert.data import HeartbeatObject, AlertObject, map_alert_object_to_arguments
from tkalert.settings import MAIL_SERVER, MAIL_TARGET_ADDRESS, GNUPG_KEY
from tkalert.mail import Mailer
from tkalert.gnupg import GnupgInterface

def main():
    """Main script to trigger alerting
        Returns:
            int. The return code
    """

    myoptions = MyOptions(usage="%prog --type=<heartbeat|alert> [--help]",
                          version="%prog " + tkalert.__version__)

    try:
        (options, args) = myoptions.parse_args()

        if options.verbose is True:
            logging.getLogger().setLevel(logging.DEBUG)

        log = logging.getLogger(__name__)
        log.debug('Starting up')

        xml_object = None

        if options.type == "heartbeat":
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

        data = ""

        if options.noenc is True:
            data = str(xml_object)
        else:
            gpg = GnupgInterface(options.gnupgconfig)
            data = gpg.crypt_ascii(GNUPG_KEY, str(xml_object))

        mailer = Mailer()
        mailer.server = MAIL_SERVER
        mailer.receiver = MAIL_TARGET_ADDRESS
        mailer.sender = options.mail
        mailer.sender_name = options.person
        mailer.alert_type = options.type
        mailer.content = data
        mailer.send()

    except MyOptionIsMandatoryError as e:
        print(e.message + "\n")
        myoptions.print_usage()
        return 255
    return 0

if __name__ == "__main__":
    sys.exit(main())