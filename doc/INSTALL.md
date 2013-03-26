TKMON web
=========

Installation
------------

### Prerequisites

Base system was a **Ubuntu LTS 12.04 Precise**

To get the system running you'll need to have some mandatory things:

* PHP > 5.3 (Web and CLI)
* Extensions installed: pdo-sqlite
* Apache2

To get a working base monitoring you'll also need:

* tkalert
* IPMI checkplugin (nagios-plugins-contrib with ipmi perl version)

### Ubuntu install

#### Package install

    apt-get install apache2 php5 php5-cli php5-sqlite php5-curl php5-xcache libapache2-mod-php5 postfix \
        sqlite3 zip unzip

#### Testinstall IPMI Plugin
    cd /root
    wget "http://ftp.us.debian.org/debian/pool/main/n/nagios-plugins-contrib/nagios-plugins-contrib_4.20120702_amd64.deb" -O nagios-plugins-contrib_4.20120702_amd64.deb
    dpkg -i nagios-plugins-contrib_4.20120702_amd64.deb
    apt-get install libipc-run-perl freeipmi-tools

#### PHP configuration

Enable XCache variable cache.

A cache is needed to cache the service catalogues. This catalogues are loaded once at first start. After that, data resides in apache memory.

    # grep var_ /etc/php5/conf.d/xcache.ini
    xcache.var_size  =            16M
    xcache.var_count =             1
    xcache.var_slots =            8K
    xcache.var_ttl   =             0
    xcache.var_maxttl   =          0
    xcache.var_gc_interval =     300

Important is to set xcache.var_size to 16M. Restart apache after that.

#### Apache configuration

    cp etc/apache2/conf.d/tkmon.conf /etc/apache2/conf.d/tkmon.conf
    a2enmod rewrite
    /etc/init.d/apache2 restart

#### Test install

    apt-get install php-pear
    pear config-set auto_discover 1
    pear install pear.phpunit.de/PHPUnit

#### Run tests

To run all possible unit tests type

    phpunit

To run integration tests (where icinga and other resource files are installed) type

     phpunit  --group integration

### Copy sources

Copy the sources to a safe place, e.g. /opt/tkmon-web or /usr/local/src/tkmon-web

    {core.var_dir}/cache
    {core.var_dir}/db

The webserver needs to write data into theese directories.

### Install PHP Composer

In this project some certain libraries are used to build the functionallity. To
resolved the dependencies you'll have to use php composer to fix that.

More information can be found [on its website](http://getcomposer.org/). But I'll show
some basic steps to do this.

#### Local install

This means you do install the composer in the project directory to keep your
system clean from unpackaged third-party tools:

    $ cd tk-mon/
    $ $ curl -s https://getcomposer.org/installer | php
      #!/usr/bin/env php
      All settings correct for using Composer
      Downloading...

      Composer successfully installed to: /data/users/mhein/workspaces/tk-mon/tk-mon/composer.phar
      Use it: php composer.phar
    $ php composer.phar install
    Loading composer repositories with package information
    Installing dependencies
      - Installing twig/twig (v1.11.1)
        Loading from cache

      - Installing pimple/pimple (dev-master v1.0.1)
        Cloning v1.0.1

    Writing lock file
    Generating autoload files

Thats is. The software is ready to run

### Sudoers file

Tkmon runs a couple of commands with root privileges you need to allow for the web user.

#### Copy sudoers

    cp etc/sudoers.d/tkmon /etc/sudoers.d/tkmon
    chmod 440 /etc/sudoers.d/tkmon

#### Add admin group and allow web user

    addgroup --system tkmonweb
    adduser www-data tkmonweb
    service apache2 restart

### Install Icinga

We'll install icinga with some special configurations and paths to get managed
with the appliance.

#### Install PPA repository

    aptitude install python-software-properties
    add-apt-repository ppa:formorer/icinga
    aptitude update
    aptitude install icinga

#### Add TK admin user to icinga

    cd /etc/icinga
    # Add password from config.json
    htpasswd -b htpasswd.users "tkadmin" "7RMan59XmN9t3FO2evmB"
    # Add tkadmin to cgi.cfg
    sed -i.BAK -e 's/icingaadmin/icingaadmin,tkadmin/g' cgi.cfg

#### Configure icinga

    cd /etc/icinga
    ln -s /path/to/tkmon/etc/icinga/ tkmon

Change icinga.cfg as follows

    diff -u  icinga.cfg.org icinga.cfg
    --- icinga.cfg.org	2013-01-18 12:14:24.316237780 +0000
    +++ icinga.cfg	2013-01-18 12:15:56.751081775 +0000
    @@ -29,10 +29,9 @@
     # Hint: Check the docs/wiki on how to monitor remote hosts with different
     # transport methods and plugins

    -# Debian uses by default a configuration directory where icinga-common,
    -# other packages and the local admin can dump or link configuration
    -# files into.
    -cfg_dir=/etc/icinga/objects/
    +cfg_dir=/etc/icinga/tkmon/base
    +cfg_dir=/etc/icinga/tkmon/system/templates
    +cfg_dir=/etc/icinga/tkmon/system/contacts
    +cfg_dir=/etc/icinga/tkmon/system/hosts
    +cfg_dir=/etc/icinga/tkmon/custom

     # Definitions for ido2db process checks
     #cfg_file=/etc/icinga/objects/ido2db_check_proc.cfg

Fix some privileges. This is needed to allow www-data (the apache) to write into
our config directories. And apache is writing or configuration now.

    chown -R www-data:www-data /path/to/tkmon/etc/icinga/

### Packaging fixes

#### Database

The default database configuration for source package is something like this
(config.json, debconf related part):

    "db.autocreate":        true,
    "db.debconf.use":       false,
    "db.debconf.file":      "{core.etc_dir}/config-db.php",

This means that configuration is taken from config.json (paths, files) and a
default datase is created for you. To create your own database change setting
to this please:

    "db.autocreate":        false,
    "db.debconf.use":       true,
    "db.debconf.file":      "/etc/tkmon/config-db.php",

This uses the config-db.php file to configure your connection and switch
db autocreation off.

#### JSON core variables

To fix FHS directories tkmon checks if /etc/tkmon exists and use this directory for configuration. If you
want to rewrite the pats for packaging examine config.json for the core objects:

    "core.lib_dir":         "{core.root_dir}/lib/tkmon",
    "core.share_dir":       "{core.root_dir}/share/tkmon",
    "core.template_dir":    "{core.share_dir}/templates",
    "core.var_dir":         "{core.temp_dir}/tkmon",
    "core.cache_dir":       "{core.var_dir}/cache",

Also you can override some hidden settings:

    "core.root_dir":        "",
    "core.etc_dir":         "",
    "core.tmp_dir":         "",

### Done

You are ready now to open your browser and go to you configured location.