"""
    module:: settings
"""
import tkalert

VERSION_STRING = "%s/%s" % (tkalert.__name__, tkalert.__version__)

AUTH_CATEGORY = 'Monitoring'

XML_INTERFACE_VERSION = tkalert.__version__

XML_DATE_FORMAT = '%a %b %d %H:%M:%S %Y'

MAIL_SERVER = 'localhost'

MAIL_TARGET_ADDRESS = 'mhein@netways.de'

GNUPG_KEY = '0x9B6B1E58'