TKMON web
=========

Installation
------------

### Prerequisites

To get the system running you'll need to have some mandatory things:

* PHP > 5.3 (Web and CLI)
* Extensions installed: pdo-sqlite
* Apache2

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

### Done

You are ready now to open your browser and go to you configured location.