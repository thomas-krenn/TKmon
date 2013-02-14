import sys

from tkalert.options import MyOptions, MyOptionIsMandatoryError
from tkalert.data import HeartbeatObject, AlertObject, NotACategoryError

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

        print(xml_object)

    except MyOptionIsMandatoryError as e:
        print(e.message + "\n")
        myoptions.print_usage()
        return 255
    return 0

if __name__ == "__main__":
    sys.exit(main())