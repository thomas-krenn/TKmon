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
    module:: settings
"""

import tkalert

VERSION_STRING = "%s/%s" % (tkalert.__name__, tkalert.__version__)

AUTH_CATEGORY = 'Monitoring'

XML_INTERFACE_VERSION = tkalert.__version__

XML_DATE_FORMAT = '%a %b %d %H:%M:%S %Y'

MAIL_SERVER = 'localhost'

MAIL_TARGET_ADDRESS = 'monitoring@thomas-krenn.com'

GNUPG_KEY = '0x9B6B1E58'
