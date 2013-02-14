import sys

from tkalert.options import MyOptions, MyOptionIsMandatoryError
from tkalert.data import HeartbeatObject, AlertObject, map_alert_object_to_arguments

def main():
    """Main script to trigger alerting
        Returns:
            int. The return code
    """

    myoptions = MyOptions();
    try:
        (options, args) = myoptions.parse_args()

        xml_object = None

        if options.type == "heartbeat":
            xml_object = HeartbeatObject()
        elif options.type == "service":
            xml_object = AlertObject()

        xml_object.set_authkey(options.auth)
        xml_object.set_date('NOT A DATE')

        if options.type == "service":
            map_alert_object_to_arguments(options, xml_object)

        print(xml_object)

    except MyOptionIsMandatoryError as e:
        print(e.message + "\n")
        myoptions.print_usage()
        return 255
    return 0

if __name__ == "__main__":
    sys.exit(main())