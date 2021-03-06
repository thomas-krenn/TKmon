<?php

namespace TKMON\Test;

class Container extends \Pimple
{

    public function __construct()
    {
        $this['lib_dir'] = '';
        $this['root_dir'] = '';
        $this['etc_dir'] = '';
        $this['share_dir'] = '';

        // Testing only
        $this['test_dir'] = dirname(dirname(dirname(__DIR__)));

        $this['params_class'] = '\stdClass';
        $this['params'] = $this->share(function($c) {
            return new $c['params_class'];
        });

        $this['config_class'] = '\NETWAYS\Common\ArrayObject';
        $this['config'] = $this->share(function($c) {
            $c = new $c['config_class'];
            $c['app.version.release'] = 'tkmon-test-0.0.0';

            $c['icinga.freshness'] = 999999999999;
            $c['icinga.config'] = '/etc/icinga/icinga.cfg';

            $c['icinga.apacheconfig'] = '/test/apache/config';

            $c['icinga.dir.template'] = '/etc/icinga/tkmon/system/templates';
            $c['thomaskrenn.template.host'] = 'thomas-krenn-host';
            $c['thomaskrenn.rest.serial'] = 'https://www.thomas-krenn.com/api/serials';
            $c['thomaskrenn.rest.product'] = 'https://www.thomas-krenn.com/api/products';
            return $c;
        });

        $this['template_loader'] = $this->share(function($c) {
            return new \stdClass();
        });

        $this['template'] = $this->share(function($c) {
            return new \stdClass();
        });

        $this['db_class'] = '\stdClass';
        $this['db'] = $this->share(function($c) {
            return new $c['db_class'];
        });

        $this['session_class'] = '\stdClass';
        $this['session'] = $this->share(function($c) {
            return new $c['session_class'];
        });

        $this['user_class'] = '\stdClass';
        $this['user'] = $this->share(function($c) {
            return new $c['user_class'];
        });

        $this['dispatcher_class'] = '\stdClass';
        $this['dispatcher'] = $this->share(function($c) {
            return new $c['dispatcher_class'];
        });

        $this['navigation'] = $this->share(function($c) {
            return new \stdClass();
        });

        $this['command'] = $this->share(function($c) {

            $commands_def = '{
                "chpasswd":  {
                    "path": "/usr/sbin/chpasswd",
                    "sudo": false
                },

                "usermod": {
                    "path": "/usr/sbin/usermod",
                    "sudo": false
                },

                "passwd": {
                    "path": "/usr/bin/passwd",
                    "sudo": false
                },

                "reboot": {
                    "path": "/sbin/reboot",
                    "sudo": false
                },

                "hostname": {
                    "path": "/bin/hostname",
                    "sudo": false
                },

                "mv": {
                    "path": "/bin/mv",
                    "sudo": false
                },

                "restart": {
                    "path": "/sbin/restart",
                    "sudo": false
                },

                "service": {
                    "path": "/usr/sbin/service",
                    "sudo": false
                },

                "htpasswd": {
                    "path": "/usr/bin/htpasswd"
                },

                "icinga": {
                    "path": "/usr/sbin/icinga"
                },

                "tkalert.sh": {
                    "path": "/usr/local/bin/tkalert.sh",
                    "sudo": false
                }
            }';

            $factory = new \TKMON\Model\Command\Factory();
            $factory->setCommands(json_decode($commands_def, false));

            return $factory;
        });

        $this['hostData'] = function ($c) {
            $hostData = new \TKMON\Model\Icinga\HostData($c);

            // Registering default attribute handler
            $hostData->appendHandlerToChain(new \TKMON\Extension\Host\DefaultAttributes($c));

            return $hostData;
        };

        $container['serviceCatalogue'] = function ($c) {
            $catalogue = new \ICINGA\Catalogue\Services();

            return $catalogue;
        };
    }
}
