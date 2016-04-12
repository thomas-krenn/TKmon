# TKMON-WEB JSON service catalogue how-to

TKMON-WEB uses catalogue systems to determine which services can be added to hosts. This services can be configured with JSON files or any other API’s like REST endpoints.

## Under the hood

To get a catalogue service work you’ll need up to three parts:

### Catalogue definition

This is a meta description for the service, contains data like name, description of tags. Also parameters which can be configured (warning thresholds, ports, hosts, passwords and such things).

The important thing is the name of the **check command** which is used on appliance side to realize the check.

Each of this definitions have a unique ID (which is not the service name and not the command name). This **ID is stored as customvar on the service**. This helps to identify the definition if you want to modify an existing service (And changed the service name for example).

This is a example definition:

```
{
    "service_description": "net-ping",
    "display_name": "Ping check",
    "check_command": {
        "command_name": "check_ping",
        "arguments": [
            {
                "label": "Warning threshold",
                "description": "In form <0000,000%>, <milliseconds rtt>,<percent rta><%>",
                "validation": "string",
                "argument": "-w",
                "value": "5000,100%"
            },
            {
                "label": "Critical threshold",
                "description": "In form <0000,000%>, <milliseconds rtt>,<percent rta><%>",
                "validation": "string",
                "argument": "-c",
                "value": "5000,100%"
            }
        ]
    },
    "_catalogue_attributes": {
        "tags": ["remote", "ping", "network"],
        "name": "net-ping",
        "label": "Ping check",
        "description": "Check packet loss and round trip time",
        "defined": true,
        "defined_in": "/etc/nagios-plugins/config/ping.cfg"
    }
}
```

This example is defined in JSON. Have a look at the key **<span class="red">"_catalogue_attributes"</span>**. There a meta data stored for use in the catalogue system: Searching, identifying and path information.

A concrete service definition which implements the "catalogue" ping definition looks like this:

```
# Define object ipmi-test01_net-ping (service)
define service {
    service_description           net-ping
    display_name                  Ping check
    use                           thomas-krenn-service
    host_name                     ipmi-test01
    check_command                 check_ping!1000,30%!1000,30%
    # Dump custom variables
    _TAGS                         remote, ping, network
    _NAME                         net-ping
    _LABEL                        Ping check
    _DESCRIPTION                  Check packet loss and round trip time
}
```

The custom vars are created automatically and refers to the catalogue service definition. In any case, **you to not need** to edit this values manually.

### Icinga check command

A corresponding check command to the catalogue definition is needed to create a concrete service. This check command must be defined in icinga outside from any dynamic configuration, e.g. through the nagios-plugins project (/etc/nagios-plugins/config/) which is included in icinga.cfg or with your own config, which can look like this:

```
# head -5 /etc/icinga/tkmon/base/05-check-commands.cfg
# Derived from debian command to match send expect rules
define command {
        command_name    check_udp_send_expect
        command_line    /usr/lib/nagios/plugins/check_udp -H '$HOSTADDRESS/div> -p '$ARG1/div> -s '$ARG2/div> -e '$ARG3/div>
        }
```

The matching record looks like this: (defined in etc/tkmon/services-custom.json)

```
{
    "service_description": "net-udp",
    "display_name": "UDP Port",
    "check_command": {
        "command_name": "check_udp_send_expect",
        "arguments": [
            {
                "label": "UDP Port",
                "description": "Integer value of UDP port to test",
                "validation": "port",
                "argument": "-p"
            },
            {
                "label": "Send string",
                "description": "String, what to send",
                "validation": "string",
                "argument": "-s"
            },
            {
                "label": "Expect string",
                "description": "String, what to expect as answer",
                "validation": "string",
                "argument": "-e"
            }
        ]
    },
    "_catalogue_attributes": {
        "tags": ["remote", "udp", "port", "network"],
        "name": "net-udp",
        "label": "UDP port check",
        "description": "Check udp port reaction (derived from debian default)",
        "defined": true,
        "defined_in": "/etc/icinga/tkmon/base/05-check-commands.cfg"
    }
}
```

The connecting items are:

<dl>

<dt class="hdlist1">command_name</dt>

<dd>

Refers to the "real" icinga check command

</dd>

<dt class="hdlist1">arguments</dt>

<dd>

Configures 3 arguments, and the command uses $ARG1$, $ARG2$, $ARG3$

</dd>

</dl>

All other things are only "chacka-lacka" and used to create human readable description for a possible service.

### Remote site package

Till now we have only remote services in the TKMON stack. These are the only services, we can guarantee to work on "every" remote side, e.g. HTTP, TCP ports SSH tests and things like that.

This brings us to some wonderful symbiosis: Remote services. For example you want to install NRPE checks you’ll need a corresponding remote configuration so you’ll need to create packages:

<dl>

<dt class="hdlist1">TKMON-WEB</dt>

<dd>

A debian package, maybe "tkmon-check-definition-nrpe-raid" which install NRPE and also calling definitions what to do on remote side (ICINGA) and also some catalogue definitions how to configure all this as an service.

</dd>

<dt class="hdlist1">CHECK-PLUGIN</dt>

<dd>

Debian package "tkmon-check-nrpe-raid" which holds plugin binaries, nrpe configuration and nrpe dependency.

</dd>

</dl>

To get a raid controller check plugin working you have to install the bundle on tkmon side and on remote side.

## Format and Config

### Config

The JSON catalogue is one instance but can be built through multiple files. The directory is configured in config.json:

```
"icinga.catalogue.services.json.dir": "{core.etc_dir}/service-catalogues",
```

This directory holds every JSON files. Per default you have two files:

<dl>

<dt class="hdlist1">services-default-debian.json</dt>

<dd>

Contains most remote services from nagios-plugins package

</dd>

<dt class="hdlist1">services-custom</dt>

<dd>

Contains derived remote services from nagios-plugins package (e.g. missing parameters for the udp port check)

</dd>

</dl>

### Format

Is [JSON](http://en.wikipedia.org/wiki/JSON). Please keep in mind to test the files because you’ll get no validation errors. Try some tools to check if your json is valid, e.g. [JSON Lint](http://jsonlint.com/)

### Container

Each file can contain multiple definitions, wrapped into a container:

```
{
    "type": "service",
    "version": "1.0",
    "description": "Services catalogue for custom checks",
    "data": [

    ]
}
```

The definitions itself are objects and to into

```
"data": [{

}, {

}]
```

### Base record

A base record is a object contains the following attributes:

<dl>

<dt class="hdlist1">**service_description**</dt>

<dd>

Icinga service attribute of service description. This is a string template which can be changed by the user while service creation. Technical name

</dd>

<dt class="hdlist1">**display_name**</dt>

<dd>

Icinga service attribute of service description. This is a string template which can be changed by the user while service creation - But a more common name, e.g. "TCP port check for HTTP - 80"

</dd>

<dt class="hdlist1">**check_command**</dt>

<dd>

Type of object, describes the check command (predefined) and their parameter needs configured by the user.

</dd>

<dt class="hdlist1">**_catalogue_attributes**</dt>

<dd>

Type of object, meta data for the catalogues. This data helps users to find their checks.

</dd>

</dl>

#### Example

```
        {
            "service_description": "net-udp",
            "display_name": "UDP Port",
            "check_command": {
                "command_name": "check_udp_send_expect",
                "arguments": [
                    {
                        "label": "UDP Port",
                        "description": "Integer value of UDP port to test",
                        "validation": "port",
                        "argument": "-p"
                    },
                    {
                        "label": "Send string",
                        "description": "String, what to send",
                        "validation": "string",
                        "argument": "-s"
                    },
                    {
                        "label": "Expect string",
                        "description": "String, what to expect as answer",
                        "validation": "string",
                        "argument": "-e"
                    }
                ]
            },
            "_catalogue_attributes": {
                "tags": ["remote", "udp", "port", "network"],
                "name": "net-udp",
                "label": "UDP port check",
                "description": "Check udp port reaction (derived from debian default)",
                "defined": true,
                "defined_in": "/etc/icinga/tkmon/base/05-check-commands.cfg"
            }
        },
```

### Check command and its arguments

The check command definition (name **check_command**) contains information about the predefined command used in the service. Also the parameters are defined to help the user through the configuration process. This object contains only two important keys:

<dl>

<dt class="hdlist1">**command_name**</dt>

<dd>

Name of the icinga command used for this. It is important that this is pre configured, working and known to icinga. Because we’re creating a service based on this command. A restart of icinga will fail if something wents wring and no documentation to the user is present why this does not work as expected. (Type: string)

</dd>

<dt class="hdlist1">**arguments**</dt>

<dd>

An array of arguments (objects) used in this command. Keys described later on. This results in icinga definition like this: check_command: <check_command>|ARG1|ARG2|ARG3

</dd>

</dl>

#### Configuring arguments

Arguments look like this:

```
{
    "label": "UDP Port",
    "description": "Integer value of UDP port to test",
    "validation": "port",
    "argument": "-p",
    "type": "text"
},
```

Following keys are used:

<dl>

<dt class="hdlist1">**label**</dt>

<dd>

Short description for the user (type string)

</dd>

<dt class="hdlist1">**description**</dt>

<dd>

Long description for the user (type string)

</dd>

<dt class="hdlist1">**validation** (unused)</dt>

<dd>

What type of value a user should enter (type string). This is not used at present but maybe later on. Value should be something like this: integer, double, string, port, hostname

</dd>

<dt class="hdlist1">**argument** (unused)</dt>

<dd>

Which argument is used for the plugin, e.g. _-p_ for _check_tcp -H 127.0.0.1 -p 80_. This is also not used at the moment, but maybe later for user documentation or automatic creation of command definitions

</dd>

<dt class="hdlist1">**type** (optional)</dt>

<dd>

Define which input type should be used when consuming in frontend. Valid valied are **text** (default) and **password**. The type is expanded to a class. Password (password) will be expanded to \TKMON\\Form\Field\Password

</dd>

</dl>

### Meta attributes

Meta attributes are used to identify checks and have nothing to do with the technical icinga side.

#### Container

Meta attributes are type of object and enclosed by this:

```
"_catalogue_attributes": {

}
```

#### Keys

<dl>

<dt class="hdlist1">**tags** (type array of strings)</dt>

<dd>

Used to _tag_ the check. Just pass keywords into the array. (Have a look into the existing checks what is used. Commonly used for network checks could be: **["remote", "tcp", "port", "network"]**

</dd>

<dt class="hdlist1">**name** (string)</dt>

<dd>

Name of check. **Important**: This must be an unique uid technical name. This name is stored as custom var to the service and used as reference if a user edit the service again.

</dd>

<dt class="hdlist1">**label** (string)</dt>

<dd>

Common name if check, user short name.

</dd>

<dt class="hdlist1">**description** (string)</dt>

<dd>

Long description for the user

</dd>

<dt class="hdlist1">**defined** (boolean, unused)</dt>

<dd>

Flag to indicate if the check is already defined. Can later used to indicate that we’ve to create this service.

</dd>

<dt class="hdlist1">**defined_in** (string, unused)</dt>

<dd>

If **defined** this key shows where. Later use to document the check more clearly.

</dd>

<dt class="hdlist1">**links** (array of objects)</dt>

<dd>

Adds links to the service. Each sub objects must have following items: **href**, **name**, **description**. Every part can be translated with the model explained in appendix **D**.

</dd>

<dt class="hdlist1">**doc** (array of objects or array)</dt>

<dd>

Inline doc strings to provide basic information for service checks. HTML can be used, multiline is done via **array** notation. See appendix **F** for more information.

</dd>

<dt class="hdlist1">****tk_notify**** (boolean)</dt>

<dd>

Thomas-Krenn notification flag, if set this service can reported to Thomas-Krenn.

</dd>

<dt class="hdlist1">****tk_notify_default**** (boolean)</dt>

<dd>

Default of notification set. Set to **true** the widget is selected to **Yes, report this service to Thomas-Krenn**

</dd>

</dl>

## Appendix

### A: Full JSON example

```
{
    "type": "service",
    "version": "1.0",
    "description": "Services catalogue for custom checks",
    "data": [
        {
            "service_description": "net-udp",
            "display_name": "UDP Port",
            "check_command": {
                "command_name": "check_udp_send_expect",
                "arguments": [
                    {
                        "label": "UDP Port",
                        "description": "Integer value of UDP port to test",
                        "validation": "port",
                        "argument": "-p"
                    },
                    {
                        "label": "Send string",
                        "description": "String, what to send",
                        "validation": "string",
                        "argument": "-s"
                    },
                    {
                        "label": "Expect string",
                        "description": "String, what to expect as answer",
                        "validation": "string",
                        "argument": "-e"
                    }
                ]
            },
            "_catalogue_attributes": {
                "tags": ["remote", "udp", "port", "network"],
                "name": "net-udp",
                "label": "UDP port check",
                "description": "Check udp port reaction (derived from debian default)",
                "defined": true,
                "defined_in": "/etc/icinga/tkmon/base/05-check-commands.cfg"
            }
        },
        {
            "service_description": "ipmi-tkmon-custom1",
            "display_name": "IPMI CustomVars",
            "check_command": {
                "command_name": "check_ipmi_tkmon_custom1",
                "arguments": null
            },
            "_catalogue_attributes": {
                "tags": ["remote", "ipmi", "hardware", "tkmon"],
                "name": "ipmi-tkmon-custom1",
                "label": "IPMI check with customvars",
                "description": "Check all IPMI parameters with customvars",
                "defined": true,
                "defined_in": "/etc/icinga/tkmon/base/05-check-commands.cfg"
            }
        }
    ]
}
```

### B: Commands corresponding to A

```
# Derived from debian command to match send expect rules
define command {
        command_name    check_udp_send_expect
        command_line    /usr/lib/nagios/plugins/check_udp -H '$HOSTADDRESS/div> -p '$ARG1/div> -s '$ARG2/div> -e '$ARG3/div>
        }

# IPMI command to match custom vars
define command {
        command_name    check_ipmi_tkmon_custom1
        command_line    /usr/lib/nagios/plugins/check_ipmi_sensor -U '$_HOSTIPMI_USER/div> -P '$_HOSTIPMI_PASSWORD/div> -L 'USER' -H '$_HOSTIPMI_IP/div>
        }
```

### C: Services based on A and B

This is what is created through API triggered by the user.

```
# Define object ipmi-test01 (host)
define host {
    host_name                     ipmi-test01
    alias                         IPMI Test
    address                       172.16.10.1
    use                           thomas-krenn-host
    # Dump custom variables
    _SERIAL                       ipmi-123987234
    _OS                           Ubuntu 12.04 LTS
    _IPMI_USER                    ADMIN
    _IPMI_PASSWORD                ADMIN
    _IPMI_IP                      192.168.100.100
    _SNMP_COMMUNITY
}

# Define object ipmi-test01_ipmi-tkmon-custom1 (service)
define service {
    service_description           ipmi-tkmon-custom1
    display_name                  IPMI CustomVars
    use                           thomas-krenn-service
    host_name                     ipmi-test01
    check_command                 check_ipmi_tkmon_custom1
    # Dump custom variables
    _TAGS                         remote, ipmi, hardware, tkmon
    _NAME                         ipmi-tkmon-custom1
    _LABEL                        IPMI check with customvars
    _DESCRIPTION                  Check all IPMI parameters with customvars
}

# Define object ipmi-test01_net-udp-113-auth (service)
define service {
    service_description           net-udp-113-auth
    display_name                  UDP Port Test DING on port 113
    use                           thomas-krenn-service
    host_name                     ipmi-test01
    check_command                 check_udp_send_expect!113!DING!DONG
    # Dump custom variables
    _TAGS                         remote, udp, port, network
    _NAME                         net-udp
    _LABEL                        UDP port check
    _DESCRIPTION                  Check udp port reaction (derived from debian default)
}
```

### D: Using localized description and labels

As default you can use object to provide translations for specific tags. Every tag can be used but only **label** or ***description** is meaningful.

Default language is **en_US** (If langague is not found or not defined).

The language locales are defined in **config.js** in setting **locale.list**.

Do not

Do not use this feature for **service_description** or **display_name**. These keys are software specific identifier and must be simple and in english.

#### How to use that

```
{
    "label": {
        "en_US": "UDP Port to test",
        "de_DE": "UDP Port welcher getestet werden soll
    },
    "description": {
        "en_US": "Integer value of UDP port to test",
        "de_DE": "Nummer des UDP Ports"
    },
    "validation": "port",
    "argument": "-p"
},
```

### E: Using links for service description

Useful links can be configured within service catalogue with the attribute **links**. The example below show a mixed translated selection of links for the IPMI plugin.

```
"_catalogue_attributes": {
    "links": [{
        "name": "IPMI Wiki",
        "href": {
            "en_US": "http://www.thomas-krenn.com/en/wiki/IPMI_Sensor_Monitoring_Plugin",
            "de_DE": "http://www.thomas-krenn.com/de/wiki/IPMI_Sensor_Monitoring_Plugin"
        },
        "description": {
            "en_US": "Official IPMI wiki article from Thomas-Krenn",
            "de_DE": "Offizieller IPMI wiki Artikel von Thomas-Krenn"
        }
    }, {
        "name": "GIT Repository",
        "href": "http://git.thomas-krenn.com/?p=check_ipmi_sensor_v3.git;a=summary",
        "description": "Thomas-Krenn GIT repository for check_ipmi_sensor"
    }]
}
```

### F: Use inline documentation

#### With translation:

```
"_catalogue_attributes": {
    "doc": {
        "en_US": [
            "<h4>IPMI Sensor Monitoring Plugin</h4>",
            "<p>Using this plugin, the hardware status of a server can be monitored by Nagios",
            "or Icinga. As specific examples, fan rotation speeds, temperatures, voltages,",
            "power consumption, power supply performance and more will be monitored.</p>"
        ]
    }
}
```

#### Without translation:

```
"_catalogue_attributes": {
    "doc": [
            "<h4>IPMI Sensor Monitoring Plugin</h4>",
            "<p>Using this plugin, the hardware status of a server can be monitored by Nagios",
            "or Icinga. As specific examples, fan rotation speeds, temperatures, voltages,",
            "power consumption, power supply performance and more will be monitored.</p>"
    ]
}
```
