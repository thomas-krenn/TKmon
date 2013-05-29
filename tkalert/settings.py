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
"Version of tkalert"

AUTH_CATEGORY = 'Monitoring'
"XML auth category, no change needed"

XML_INTERFACE_VERSION = tkalert.__version__
"Version number of xml format"

XML_DATE_FORMAT = '%a %b %d %H:%M:%S %Y'
"Date formatting string used in xml output"

MAIL_SERVER = 'localhost'
"Mailserver used"

MAIL_TARGET_ADDRESS = 'monitor@thomas-krenn.com'
"Target adress, where the mails to to"

MAIL_TEST_SUBJECT = 'Icinga Testheartbeat'
"Subject if we want to send a testmail"

GNUPG_KEY = '0x584F819C'
"GPG key id, which key is used to encrypt the mail"
