"""
    module:: mail
"""
import smtplib
import logging
from email.mime.text import MIMEText

from tkalert.settings import VERSION_STRING

__ALL__ = ['Mailer']

log = logging.getLogger(__name__)


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

        log.debug("Send mail to %s (server=%s)", self.receiver, self.server)

        server = smtplib.SMTP(self.server)
        server.sendmail(self.sender, self.receiver, message.as_string())
