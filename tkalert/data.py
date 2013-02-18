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
    module:: data
"""

from datetime import datetime
from xml.dom.minidom import Document

from tkalert.settings import AUTH_CATEGORY, XML_INTERFACE_VERSION \
    , XML_DATE_FORMAT

__all__ = ['HeartbeatObject', 'AlertObject', 'map_alert_object_to_arguments']


class XmlStructure(Document):
    """Class to build xml structures for alert objects"""
    def __init__(self, alertType):
        Document.__init__(self)
        self.root_node = self.createElement(alertType)
        self.root_node.setAttribute('version', XML_INTERFACE_VERSION)

        self.authkey_node = self.createElement('authkey')
        self.authkey_node.setAttribute('category', AUTH_CATEGORY)

        self.date_node = self.createElement('date')

        self.contact_name_node = self.createElement('contact-name')
        self.contact_mail_node = self.createElement('contact-mail')

        self.root_node.appendChild(self.authkey_node)
        self.root_node.appendChild(self.date_node)
        self.root_node.appendChild(self.contact_name_node)
        self.root_node.appendChild(self.contact_mail_node)

        self.appendChild(self.root_node)

    def get_type(self):
        """Getter for object type

        Returns:
            Object type, e.g. heartbeat or service (String)
        """
        return self.root_node.nodeName

    def get_authkey(self):
        """Getter for authkey

        Returns:
            Name of authkey (string)
        """
        return self.authkey_node.nodeValue

    def set_authkey(self, value):
        """Setter for authkey
            Args:
                value (string)
        """
        node = self.createTextNode(value)
        self.authkey_node.appendChild(node)

    def get_date(self):
        """Getter for date

            Returns:
                date (string)
        """
        return self.date_node.nodeValue

    def set_date_to_now(self):
        """Sets current date"""
        now = datetime.now()
        self.set_date(now)

    def set_date(self, value):
        """Setter for date

            Args:
                date (string) iso formatted date
        """
        textDate = value.strftime(XML_DATE_FORMAT)
        node = self.createTextNode(textDate)
        self.date_node.appendChild(node)

    def set_contact_name(self, value):
        """Setter for contact name

         Args:
            value (string) name of contact person
        """
        node = self.createTextNode(value)
        self.contact_name_node.appendChild(node)

    def get_contact_name(self):
        """Getter for contact name

        Returns: contact name (string)
        """
        return self.contact_name_node.nodeValue

    def set_contact_email(self, value):
        """Setter for contact email
        Args:
            value (string) email address of contact person
        """
        node = self.createTextNode(value)
        self.contact_mail_node.appendChild(node)

    def get_contact_email(self):

        return self.contact_mail_node.nodeValue

    def __str__(self):
        """ String representation of this object

        Returns:
            XML output (string)
        """
        return self.toprettyxml(indent='  ', encoding="UTF-8")


class HeartbeatObject(XmlStructure):
    """Heartbeat object, configured type"""
    def __init__(self):
        XmlStructure.__init__(self, 'heartbeat')


class AlertObject(XmlStructure):
    """Structure for an alert

    This object holds information about host / service details

    """

    _host_list = ['name', 'ip', 'status', 'operating-system', 'server-serial']

    _service_list = ['name', 'status', 'plugin-output', 'perfdata',
                     'duration', 'component-serial', 'component-name']

    _host_items = {}

    _service_items = {}

    def __init__(self):
        """Create a new object"""
        XmlStructure.__init__(self, 'alert')
        self.host_node = self.createElement('host')
        self.service_node = self.createElement('service')

        for host_item in self._host_list:
            self._host_items[host_item] = self.createElement(host_item)
            self.host_node.appendChild(self._host_items[host_item])

        for service_item in self._service_list:
            self._service_items[service_item] = self.createElement(service_item)
            self.service_node.appendChild(self._service_items[service_item])

        self.root_node.appendChild(self.host_node)
        self.root_node.appendChild(self.service_node)

    def set_service_value(self, key, value):
        """Set service values

        Args:
            key (string) data key which is a xml element
            value (string) node content

        """
        node = self._service_items[key]
        text = self.createCDATASection(str(value))
        node.appendChild(text)

    def set_host_value(self, key, value):
        """Set host values

        Args:
            key (string) data key which is a xml element
            value (string) node content

        """
        node = self._host_items[key]
        text = self.createCDATASection(str(value))
        node.appendChild(text)


SERVICE_MAP = {
    'service': 'name',
    'servicestatus': 'status',
    'output': 'plugin-output',
    'perf': 'perfdata',
    'duration': 'duration',
    'componentserial': 'component-serial',
    'componentname': 'component-name'
}

HOST_MAP = {
    'host': 'name',
    'ip': 'ip',
    'hoststatus': 'status',
    'os': 'operating-system',
    'serial': 'server-serial'
}


def map_alert_object_to_arguments(options, xml):
    """Attribute mapper

    Maps attributes from OptionParser to xml object (AlertObject)

    Args:
        options (OptionParser)
        xml (AlertObject)

    """
    for attrib_name, xml_key in SERVICE_MAP.items():
        if getattr(options, attrib_name) is not None:
            xml.set_service_value(xml_key, getattr(options, attrib_name))
    for attrib_name, xml_key in HOST_MAP.items():
        if getattr(options, attrib_name) is not None:
            xml.set_host_value(xml_key, getattr(options, attrib_name))