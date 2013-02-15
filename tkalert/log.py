"""
    module:: log

    This module was bought from inGraph (https://git.netways.org/ingraph) (gplv2 (2) 2013 NETWAYS GmbH)

    Thanks to Eric Lippmann writing this code
"""

import sys
import logging

__ALL__ = []

class LogFormatter(logging.Formatter):
    """Log formatter with color support used in inGraph.
    This formatter is enabled automatically by importing `~ingraph.log`."""
    def __init__(self, *args, **kwargs):
        logging.Formatter.__init__(self, *args, **kwargs)
        self._color = sys.stderr.isatty()
        if self._color:
            self._colors = {
                logging.DEBUG: ('\x1b[34m',), # Blue
                logging.INFO: ('\x1b[32m',), # Green
                logging.WARNING: ('\x1b[33m',), # Yellow
                logging.ERROR: ('\x1b[31m',), # Red
                logging.CRITICAL: ('\x1b[1m', '\x1b[31m'), # Bold, Red
            }
            self._footer = '\x1b[0m'

    def format(self, record):
        formatted_message = logging.Formatter.format(self, record)
        if self._color:
            formatted_message = (''.join(self._colors.get(record.levelno)) + formatted_message +
                                 len(self._colors.get(record.levelno)) * self._footer)
        return formatted_message

channel = logging.StreamHandler()
channel.setFormatter(LogFormatter(fmt='%(asctime)s [%(levelname)s] %(message)s'))
logging.getLogger().addHandler(channel)