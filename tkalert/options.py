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
    module:: options
"""

from optparse import OptionParser, OptionGroup

__all__ = ["MyOptions", "MyOptionIsMandatoryError"]


class MyOptionIsMandatoryError(Exception):
    """Mandatory exception"""
    pass


class MyOptions(OptionParser):
    """Custom option class

    Handle massive amount of options
    """
    _my_groups = [
        ('general', 'Alert', 'General settings'),
        ('host', 'Host data', 'All data that belongs to the host'),
        ('service', 'Service data', 'Data from failing service'),
        ('debug', 'Debug settings', 'Test data and results')
    ]

    _my_type_choices = ('heartbeat', 'service')

    _my_options = [
        {'name': '--type',
         'dest': 'type',
         'default': 'heartbeat',
         'help': "Type of alert. One of %s [default: %%default]"
                 % ','.join(_my_type_choices),
         'choices': _my_type_choices,
         'type': 'choice',
         'group': 'general'},
        {'name': '--auth-key',
         'dest': 'auth',
         'help': 'Auth key from Thomas Krenn',
         'group': 'general'},
        {'name': '--contact-person',
         'dest': 'person',
         'help': 'Name of contact person',
         'group': 'general'},
        {'name': '--contact-mail',
         'dest': 'mail',
         'help': 'Mail address',
         'group': 'general'},
        {'name': '--date',
         'dest': 'date',
         'help': 'Timestamp of alert (optional)',
         'optional': True,
         'metavar': 'UNIXEPOCH',
         'group': 'general'},
        {'name': '--gnupg-config',
         'dest': 'gnupgconfig',
         'help': 'Path to configuration file',
         'metavar': 'FILE',
         'group': 'general'},

        {'name': '--host',
         'dest': 'host',
         'help': 'Icinga name of host',
         'group': 'host'},
        {'name': '--ip',
         'dest': 'ip',
         'help': 'IPv4 address if host',
         'group': 'host'},
        {'name': '--host-status',
         'dest': 'hoststatus',
         'help': 'Host status value',
         'group': 'host'},
        {'name': '--os',
         'dest': 'os',
         'help': 'Brief operating system description',
         'group': 'host'},
        {'name': '--serial',
         'dest': 'serial',
         'help': 'TK hardware serial',
         'group': 'host'},

        {'name': '--service',
         'dest': 'service',
         'help': 'Icinga name of service',
         'group': 'service'},
        {'name': '--service-status',
         'dest': 'servicestatus',
         'help': 'Icinga servicestatus value',
         'group': 'service'},
        {'name': '--output',
         'dest': 'output',
         'help': 'Plugin output from check',
         'group': 'service'},
        {'name': '--perf',
         'dest': 'perf',
         'help': 'Perfdata from check',
         'optional': True,
         'group': 'service'},
        {'name': '--duration',
         'dest': 'duration',
         'type': 'int',
         'metavar': 'SECONDS',
         'help': 'Seconds since occurrence',
         'group': 'service'},
        {'name': '--component-serial',
         'dest': 'componentserial',
         'help': 'Serial number of TK component (if any)',
         'optional': True,
         'group': 'service'},
        {'name': '--component-name',
         'dest': 'componentname',
         'help': 'Name of failed component (if any)',
         'optional': True,
         'group': 'service'},

        {'name': '--dump-xml',
         'metavar': 'FILE',
         'dest': 'dumpxml',
         'help': 'Dump xml data to FILE',
         'optional': True,
         'group': 'debug'},
        {'name': '--verbose',
         'dest': 'verbose',
         'action': 'store_true',
         'help': 'More verbose output',
         'optional': True,
         'group': 'debug'},
        {'name': '--disable-gpg-encryption',
         'dest': 'noenc',
         'optional': True,
         'action': 'store_true',
         'help': 'Send unencrypted mail (WARNING)',
         'group': 'debug'}
    ]

    def __init__(self, *args, **kwargs):
        OptionParser.__init__(self, *args, **kwargs)
        groups = self._create_groups(self._my_groups)
        self._add_my_options(self._my_options, groups)

    def parse_args(self, *args, **kwargs):
        (options, args) = OptionParser.parse_args(self, *args, **kwargs)

        for item in self._my_options:

            if options.type == "heartbeat" and item['dest'] != "auth":
                continue

            if 'optional' in item and item['optional'] is True:
                continue

            if getattr(options, item['dest']) is None:
                raise MyOptionIsMandatoryError('Option is mandatory: ' +
                                               item['name'])

        return options, args

    def _create_groups(self, data):
        """Create OptionGroup based on dict

        Args:
            data (Array): Array if group def

        Returns:
            Dict of option group

        """
        groups = dict()
        for item in data:
            groups[item[0]] = OptionGroup(self, item[1], item[2])
        return groups

    def _add_my_options(self, options, groups):
        """Add defined options to the parser

        Args:
            options (Array): Array of option def
            groups (Dict): Dict of OptionGroup

        Returns:
            Nothing
        """
        for option in options:
            group = groups[option['group']]
            items = option.copy()
            try:
                del items['name']
                del items['group']
                del items['optional']
            except KeyError:
                pass

            group.add_option(option['name'], **items)

        for tup in self._my_groups:
            self.add_option_group(groups[tup[0]])
