"""
    module:: data
"""

from xml.dom.minidom import Document

from tkalert.settings import AUTH_CATEGORY, XML_INTERFACE_VERSION

__all__ = ['HeartbeatObject', 'AlertObject', 'map_alert_object_to_arguments']


class XmlStructure(Document):
    def __init__(self, type):
        Document.__init__(self)
        self.root_node = self.createElement(type)
        self.root_node.setAttribute('version', XML_INTERFACE_VERSION)

        self.authkey_node = self.createElement('authkey')
        self.authkey_node.setAttribute('category', AUTH_CATEGORY)

        self.date_node = self.createElement('date')

        self.root_node.appendChild(self.authkey_node)
        self.root_node.appendChild(self.date_node)
        self.appendChild(self.root_node)

    def get_authkey(self):
        return self.authkey_node.nodeValue

    def set_authkey(self, value):
        node = self.createTextNode(value)
        self.authkey_node.appendChild(node)

    def get_date(self):
        return self.date_node.nodeValue

    def set_date(self, value):
        node = self.createTextNode(value)
        self.date_node.appendChild(node)

    def __str__(self):
        return self.toprettyxml(encoding="UTF-8")


class HeartbeatObject(XmlStructure):
    def __init__(self):
        XmlStructure.__init__(self, 'heartbeat')


class AlertObject(XmlStructure):
    _host_list = ['name', 'ip', 'status', 'operating-system', 'server-serial']

    _service_list = ['name', 'status', 'plugin-output', 'perfdata',
                     'duration', 'component-serial', 'component-name']

    _host_items = {}

    _service_items = {}

    def __init__(self):
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
        node = self._service_items[key]
        text = self.createCDATASection(value)
        node.appendChild(text)

    def set_host_value(self, key, value):
        node = self._host_items[key]
        text = self.createCDATASection(value)
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
    for attrib_name, xml_key in SERVICE_MAP.items():
        if getattr(options, attrib_name) is not None:
            xml.set_service_value(xml_key, getattr(options, attrib_name))
    for attrib_name, xml_key in HOST_MAP.items():
        if getattr(options, attrib_name) is not None:
            xml.set_host_value(xml_key, getattr(options, attrib_name))