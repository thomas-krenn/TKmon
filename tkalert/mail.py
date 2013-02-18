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
    module:: mail
"""

import smtplib
import logging
from email.mime.text import MIMEText

from tkalert.settings import VERSION_STRING

__ALL__ = ['Mailer']

LOG = logging.getLogger(__name__)


class Mailer(object):
    """Sends a special formatted mail"""
    def __init__(self):
        self.server = None
        self.sender = None
        self.receiver = None
        self.sender_name = None
        self.content = None
        self.alert_type = None

    def get_sender_string(self):
        """Create sender name (with mail)

        Returns:
            Name of sender (string)
        """
        return "%s <%s>" % (self.sender_name, self.sender)

    def send(self):
        """Send mail to the air"""
        message = MIMEText(str(self.content))

        subject = 'TKMON MESSAGE (type=%s, from=%s)' % (self.alert_type, self.sender_name)

        message['Subject'] = subject
        message['From'] = self.get_sender_string()
        message['To'] = self.receiver
        message['X-Mailer'] = VERSION_STRING

        LOG.debug("Send mail to %s (server=%s)", self.receiver, self.server)

        server = smtplib.SMTP(self.server)
        server.sendmail(self.sender, self.receiver, message.as_string())
