TKALERT v0.0.2

TKALERT
=======

TKALERT is a icinga(tm)/nagios(tm) alerter script for Thomas Krenn monitoring services. This script sends encrypted
monitoring data to Thomas Krenn to maximize service for Thomas Krenn hardware


Privacy policy
--------------

We know that services like this are called "home caller" and you may feel that this could be risky. Please
read the privacy policy on the Thomas Krenn website: [http://www.thomas-krenn.com](http://www.thomas-krenn.com).

To reduce problems this tool is open source and you can debug the data which is sent to Thomas Krenn. All data
is encrypted and only data to provide this service to you is sent to Thomas Krenn


Installation
------------

We're using python setup tools here, to install just simple type (as root)

    $ aptitude install python-setuptools # INSTALL python-setuptools
    $ python setup.py install


### Test binary

After installation you should have following binary and configuration directory:

    $ ls -lah /usr/local/bin/tkalert.sh
    $ ls -lah /etc/tkalert

You do not need to configure something within /etc/tkalert because this is only GNUPG configuration and
keys.


### Some notes on GNUPG

GNUPG needs a safe directory to execute well. Means the executing owner of the alerter script have to be owner
of the configuration directory (/etc/tkalert) and the mode have to be set to 0700.

This means if you call this with user icinga you have to change permissions of the directory:

    # as user root
    $ chown -R icinga.icinga /etc/tkalert
    $ chmod 700 /etc/tkalert

If you do not want to do this, you have to create a sudoers entry to allow user nagios to
execute tkalert.sh as root (Add the following line to your /etc/sudoers)

    nagios    ALL=(ALL:ALL)    /usr/local/bin/tkalert.sh

On Ubuntu / Debian use the tool visudo to to this:

    # as user root
    $ visudo

Now you can execute this as user icinga:

    # as user root
    $ su -s /bin/bash nagios
    $ sudo /usr/local/bin/tkalert.sh --help



Send an alert
-------------

First you have to make sure that you have an valid Thomas Krenn service account and an auth key


### Sending heartbeat

    $ tkalert.sh \
        --type="heartbeat" \
        --auth-key="a-8745987348745" \
        --contact-person="Jean Luc Picard" \
        --contact-mail="jpicard@starfleet.foo"

This sends an heartbeat to Thomas Krenn.

If you want to see that is in your request you can type:

    $ tkalert.sh \
        --type="heartbeat" \
        --auth-key="a-8745987348745" \
        --contact-person="Jean Luc Picard" \
        --contact-mail="jpicard@starfleet.foo" \
        --dump-xml=/tmp/data.xml \
        --verbose
    $ less /tmp/data.xml


### Sending service problems

Use this syntax to send service provlems to Thomas Krenn

    $ tkalert.sh \
        --type="service" \
        --auth-key="a-8745987348745" \
        --contact-person="Jean Luc Picard" \
        --contact-mail="jpicard@starfleet.foo"\
        \
        --host="NCC-1701-D" \
        --host-status="UP" \
        --ip="127.0.0.200" \
        --os="Ubuntu 12.04.2 LTS" \
        --serial="00001" \
        \
        --service="Warp drive 1" \
        --service-status="WARNING" \
        --output="You are running Warp 9.6 for 11 hours" \
        --perf="speed=9.6;duration=39600s" \
        --component-serial="1276238762" \
        --component-name="warp-core-a8973487d87" \
        \
        --duration=3600 \
        --date=1361200587


Use this alerter
----------------
To use this alerter script it's best to create master hosts with customvars. After that you can simplify your command:

    define host {
        name                        tkmon-host
        use                         generic-host
        _AUTH_KEY                   a-8745987348745
        _CONTACT_NAME               Jean Luc Picard
        _CONTACT_MAIL               jpicard@starfleet.foo
        _TK_OS                      NOT_SET
        _TK_SERIAL                  NOT_SET
        register                    0
    }

    define service {
        name                        tkmon-service
        use                         generic-service
        _TK_COMPONENT_SERIAL        NOT_SET
        _TK_COMPONENT_NAME          NOT_SET
        register                    0
    }

    define command{
            command_name    notify-service-by-thomaskrenn
            command_line    /usr/local/bin/tkalert.sh \
                --type="service" \
                --auth-key="$_HOSTAUTH_KEY$" \
                --contact-person="$_HOSTCONTACT_NAME$" \
                --contact-mail="$_HOSTCONTACT_MAIL$"\
                \
                --host="$HOSTALIAS$" \
                --host-status="$HOSTSTATE$" \
                --ip="$HOSTADDRESS$" \
                --os="$_HOSTTK_OS$" \
                --serial="$_HOSTTK_SERIAL$" \
                \
                --service="$SERVICEDESC$" \
                --service-status="$SERVICESTATE$" \
                --output="$SERVICEOUTPUT$" \
                --perf="$SERVICEPERFDATA$" \
                --component-serial="$_SERVICETK_COMPONENT_SERIAL$" \
                --component-name="$_SERVICETK_COMPONENT_NAME" \
                \
                --duration="$SERVICEDURATIONSEC$" \
                --date="$LASTSERVICECHECK$"
    }

This is a quite small example, please have a look on the [Thomas Krenn Wiki for further information]
(http://www.thomas-krenn.com/)

Command reference
=================

In this section we explain some sections of commands. For a complete reference please use **tkalert.sh --help**
to examine.

Mandatory arguments
-------------------

Some options are mandatory or evercall these are:

    --type=ALERTTYPE                    What to send. A 'service' problem or
                                        just a 'heartbeat'
    --auth-key=TK AUTH KEY              Your authkey from Thomas Krenn
    --contact-person=NAME               Person of interest.
    --contact-mail                      Who is reposible for service problems,
                                        mail support will answer

Optional arguments
------------------

To refine settings you can set optional arguments

    --date=UNIXEPOCH                    Use this option to refine check date.
                                        Otherwise it will be set to NOW()

Debug arguments
---------------

To debug settings and so how things work

    --verbose                           Print some log to shell.
                                        See single steps what we're doing here
    ----dump-xml=FILE                   Dumps XML output to a specified file. You
                                        can see what is sent to Thomas Krenn. A
                                        mail is not sent here, just a dump to
                                        filesystem.
    --disable-gpg-encryption            Do not encrypt output. Take care that you
                                        only choose this for debug purposes.