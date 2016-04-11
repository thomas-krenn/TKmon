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
import time
from email.mime.text import MIMEText

from tkalert.settings import VERSION_STRING, MAIL_TEST_SUBJECT

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
        self.test_mode = False

    def get_sender_string(self):
        """Create sender name (with mail)

        Returns:
            Name of sender (string)
        """
        return "%s <%s>" % (self.sender_name, self.sender)

    def send(self):
        """Send mail to the air"""
        message = MIMEText(str(self.content), _charset='utf-8')

        subject = 'TKMON MESSAGE (type=%s, from=%s)' % (self.alert_type, self.sender_name)

        if self.test_mode:
            subject = MAIL_TEST_SUBJECT
            LOG.debug("Set subject for testing (Subject=%s)", subject)

        message['Subject'] = subject
        message['From'] = self.get_sender_string()
        message['Envelope-From'] = self.sender
        message['Date'] = time.strftime("%a, %e %b %Y %H:%M:%S %z", time.localtime())
        message['To'] = self.receiver
        message['X-Mailer'] = VERSION_STRING

        LOG.debug("Send mail to %s (server=%s)", self.receiver, self.server)

        if "@example.com" not in self.get_sender_string().lower():
            server = smtplib.SMTP(self.server)
            server.sendmail(self.sender, self.receiver, message.as_string())
