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

### Ubuntu install

#### Package install

    apt-get install apache2 php5 php5-cli php5-sqlite libapache2-mod-php5 postfix

#### Apache configuration

    cp etc/httpd.conf /etc/apache2/conf.d/tkmon.conf
    a2enmod rewrite
    /etc/init.d/apache2 restart

#### Test install

    apt-get install php-pear
    pear config-set auto_discover 1
    pear install pear.phpunit.de/PHPUnit

#### Run tests

    phpunit

### Copy sources

Copy the sources to a safe place, e.g. /opt/tkmon-web or /usr/local/src/tkmon-web

### Fix privileges

Make shure to give important directories write access for the web server.

    chown -R www-data.www-data var/cache var/db
    chmod 755 var/cache/db

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

### Apache configuration

You need only a simple vhost to expose the project to air:

    <VirtualHost _default_:80>
        DirectoryIndex index.php
        DocumentRoot /data/users/mhein/workspaces/tk-mon/tk-mon/share/htdocs
    </VirtualHost>

### Sudoers file

Tkmon runs a couple of commands with root privileges you need to allow for the web user.

#### Copy sudoers

    cp etc/sudoers /etc/sudoers.d/tkmon
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


### Done

You are ready now to open your browser and go to you configured location.