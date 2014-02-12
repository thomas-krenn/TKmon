<?php
/**
 * This file is part of TKMON
 *
 * TKMON is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TKMON is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TKMON.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Marius Hein <marius.hein@netways.de>
 * @copyright 2012-2014 NETWAYS GmbH <info@netways.de>
 */

namespace TKMON\Binary;

use ICINGA\Catalogue\Provider\JsonFiles;
use ICINGA\Catalogue\Services;
use NETWAYS\Cache\Manager;
use NETWAYS\Cache\Provider\XCache;
use NETWAYS\Common\Config\PDOLoader;
use NETWAYS\Common\Config\PDOPersister;
use NETWAYS\Common\Config;
use NETWAYS\Common\Exception\ConfigException;
use NETWAYS\Intl\SimpleTranslator;
use TKMON\Extension\Host\DefaultAttributes;
use TKMON\Extension\Host\ThomasKrennAttributes;
use TKMON\Extension\Service\ThomasKrennNotification;
use TKMON\Model\Command\Factory;
use TKMON\Model\Database\DebConfBuilder;
use TKMON\Model\Database\Importer;
use TKMON\Model\Icinga\HostData;
use TKMON\Model\Icinga\ServiceData;
use TKMON\Model\Misc\DirectoryCreator;
use TKMON\Model\User;
use TKMON\Navigation\Container;
use TKMON\Twig\Extension;

/**
 * Executor class to run the web stack in a function scope
 * @package TKMON\Binary
 * @author Marius Hein <marius.hein@netways.de>
 */
final class Web
{
    /**
     * Inits the framework, and Output to the world
     */
    public static function run()
    {

        $dirname = dirname(__FILE__);
        $libdir = dirname(dirname($dirname));

        // -------------------------------------------------------------------------------------------------------------
        // Default PHP settings
        // -------------------------------------------------------------------------------------------------------------

        // Be brave my friend!
        ini_set('display_errors', true);
        ini_set('error_reporting', E_ALL);

        // Default file permissions 644
        umask(0022);

        $container = new \Pimple();

        // -------------------------------------------------------------------------------------------------------------
        // Build our environment to deliver content to the world
        // -------------------------------------------------------------------------------------------------------------

        /*
         * Paths environment
         */
        $container['tmp_dir'] = sys_get_temp_dir();
        $container['root_dir'] = dirname(dirname(dirname(dirname(dirname(__FILE__)))));

        $etcDirectory = DIRECTORY_SEPARATOR
            . 'etc'
            . DIRECTORY_SEPARATOR
            . 'tkmon';

        if (file_exists($etcDirectory)) {
            $container['etc_dir'] = $etcDirectory;
        } else {
            $container['etc_dir'] = $container['root_dir']
                . DIRECTORY_SEPARATOR. 'etc'
                . DIRECTORY_SEPARATOR. 'tkmon';
        }

        /**
         * Directory creator
         */
        $container['directoryCreator'] = $container->share(
            function ($c) {
                $creator = new DirectoryCreator();
                return $creator;
            }
        );

        /*
         * Cgi Params
         */
        $container['params_class'] = 'NETWAYS\Http\CgiParams';
        $container['params'] = $container->share(
            function ($c) {
                return new $c['params_class']();
            }
        );

        /**
         * Configuration object
         */
        $container['config_class'] = 'NETWAYS\Common\Config';
        $container['config'] = $container->share(
            function ($c) {
                $params = $c['params'];

                /** @var Config $config */
                $config = new $c['config_class'];

                // Path settings
                // root_dir and etc_dir are automatically detected based
                // on system. Rest is configured on config.json
                $config->set('core.root_dir', $c['root_dir']);
                $config->set('core.etc_dir', $c['etc_dir']);
                $config->set('core.temp_dir', $c['tmp_dir']);

                // Web settings
                $filename = basename($params->getParameter('SCRIPT_FILENAME', null, 'header'));
                $path = str_replace($filename, '', $params->getParameter('SCRIPT_NAME', null, 'header'));

                $requestUri = $params->getParameter('REQUEST_URI', null, 'header');

                // rewrite in progress (e.g. mod_rewrite/apache2)
                // Set ENV variable TKMON_USE_REWRITE to On
                if (getenv('TKMON_USE_REWRITE') && getenv('TKMON_USE_REWRITE') === 'On') {
                    $filename = '';
                    $config->set('web.rewrite', true);
                } else {
                    $config->set('web.rewrite', false);
                }

                $config->set('web.path', $path);
                $config->set('web.script', $path . $filename);
                $config->set('web.img_path', '{web.path}img');
                $config->set('web.port', $params->getParameter('SERVER_PORT', null, 'header'));
                $config->set('web.domain', $params->getParameter('SERVER_NAME', null, 'header'));
                $config->set('web.https', false); // TODO: This should be detected

                $config->loadFile($c['etc_dir'] . DIRECTORY_SEPARATOR . 'config.json');

                // Let user override configuration
                // @see https://www.netways.org/issues/2322
                if ($config->get('config.include') !== null) {
                    $includeFile = $config->get('config.include');
                    try {
                        $config->loadFile($includeFile);
                        $config->set('config.included', true);
                    } catch (ConfigException $e) {
                        $config->set('config.included', false);
                        // No problem that the file does not exist, just
                        // ignore in this case
                    }
                }

                // Add var and cache to dir builder
                /** @var $creator \TKMON\Model\Misc\DirectoryCreator */
                $creator = $c['directoryCreator'];
                $creator->addPath($config->get('core.var_dir'));
                $creator->addPath($config->get('core.cache_dir'));
                $creator->addPath($config->get('core.log_dir'));
                $creator->addPath($config->get('template.cache_dir'));

                return $config;
            }
        );

        /**
         * Configure logger object
         */
        $container['logger'] = $container->share(
            function ($c) {
                /** @var Config $config */
                $config = $c['config'];

                $loggerName = $config['log.root'];
                $logFormat = $config['log.format'];
                $logThreshold = $config['log.level'];

                $layout = array(
                    'class' => 'LoggerLayoutPattern',
                    'params' => array(
                        'conversionPattern' => $logFormat
                    )
                );

                if ($config['log.enable'] === true) {
                    $logConfig = array (
                        'rootLogger' => array (
                            'appenders' => array('file', 'syslog')
                        ),

                        'appenders' => array(
                            'file' => array(
                                'class' => 'LoggerAppenderFile',
                                'layout' => $layout,
                                'params' => array(
                                    'append' => true,
                                    'file' => $config['log.file'],
                                    'threshold' => $logThreshold
                                )
                            ),
                            'syslog' => array(
                                'class' => 'LoggerAppenderSyslog',
                                'layout' => array(
                                    'class' => 'LoggerLayoutSimple'
                                ),
                                'params' => array(
                                    'ident' => $config['app.name'],
                                    'threshold' => $logThreshold
                                )
                            )
                        )
                    );
                } else {
                    $logConfig = array (
                        'rootLogger' => array (
                            'appenders' => array('null')
                        ),

                        'appenders' => array(
                            'null' => array(
                                'class' => 'LoggerAppenderNull',
                                'layout' => array(
                                    'class' => 'LoggerLayoutSimple'
                                )
                            )
                        )
                    );
                }

                \Logger::configure($logConfig);

                /** @var \Logger $logger */
                $logger = \Logger::getLogger($loggerName);
                $logger->debug('Logger configured: '. $loggerName);

                return $logger;
            }
        );

        // Trigger
        $container['logger'];

        /*
         * Template engine
         */
        $container['template_loader'] = $container->share(
            function ($c) {
                return new \Twig_Loader_Filesystem($c['config']->get('core.template_dir'));
            }
        );

        $container['template'] = $container->share(
            function ($c) {

                $attributes = array();

                if ($c['config']->get('template.cache', false) === true) {
                    $attributes['cache'] = $c['config']->get('template.cache_dir');
                }

                $twig = new \Twig_Environment(
                    $c['template_loader'],
                    $attributes
                );

                $twig->addExtension(new Extension($c));
                $twig->addExtension(new \Twig_Extensions_Extension_I18n());

                return $twig;
            }
        );

        /*
         * Cache
         */
        $container['cache'] = $container->share(
            function ($c) {
                $provider = new XCache();
                $cache = new Manager($provider);
                return $cache;
            }
        );

        /*
         * Database
         */
        $container['dbbuilder'] = $container->share(
            function ($c) {
                $config = $c['config'];

                $builder = new DebConfBuilder();

                if ($config['db.debconf.use'] === true) {
                    $builder->loadFromFile($config['db.debconf.file']);
                } else {
                    $builder->setType($config['db.type']);
                    $builder->setBasePath($config['db.basepath']);
                    $builder->setName($config['db.name']);
                }

                return $builder;
            }
        );

        $container['db'] = $container->share(
            function ($c) {
                /** @var $config \NETWAYS\Common\Config */
                $config = $c['config'];
                /** @var $builder \TKMON\Model\Database\DebConfBuilder */
                $builder = $c['dbbuilder'];

                /** @var $creator \TKMON\Model\Misc\DirectoryCreator */
                $creator = $c['directoryCreator'];
                $creator->addPath($builder->getBasePath());

                // Call path creator to create missing paths:
                // - All paths should exist before database is needed
                $creator->createPaths();

                if ($config['db.autocreate'] === true) {
                    $file = $builder->getBasePath(). DIRECTORY_SEPARATOR. $builder->getName();
                    $importer = new Importer();
                    $importer->setDatabase($file);
                    $importer->setSchema($config['db.schema']);
                    if (!$importer->databaseExists()) {
                        $importer->createDefaultDatabase();
                    }
                }

                $dbo = $builder->buildConnection();

                // Load additional settings from database
                $pdoLoader = new PDOLoader($dbo);
                $pdoLoader->setTable('config');
                $pdoLoader->setKeyColumn('name');
                $pdoLoader->setValueColumn('value');

                $c['config']->load($pdoLoader);

                // Configure persister to write data back
                $pdoPersister = new PDOPersister($dbo);
                $pdoPersister->setTable('config');
                $pdoPersister->setKeyColumn('name');
                $pdoPersister->setValueColumn('value');

                $c['config']->setPersister($pdoPersister);

                return $dbo;
            }
        );

        // Trigger the database object to have it ready imported
        $container['db'];

        /*
         * Session
         */
        $container['session_class'] = 'NETWAYS\Http\Session';
        $container['session'] = $container->share(
            function ($c) {
                $config = $c['config'];

                $session = new $c['session_class']();
                $session->setName($config->get('session.name'));
                $session->setLifetime($config->get('session.lifetime'));
                $session->setDomain($config->get('web.domain'));
                $session->setIsSecured($config->get('web.https'));
                $session->setPath($config->get('web.path'));

                $session->start();

                return $session;
            }
        );

        /*
         * User
         */
        $container['user_class'] = '\TKMON\Model\User';
        $container['user'] = $container->share(
            function ($c) {
                $user = new User($c);
                $user->initialize();
                return $user;
            }
        );

        /*
         * Dispatcher
         */
        $container['dispatcher_class'] = '\TKMON\Mvc\Dispatcher';
        $container['dispatcher'] = $container->share(
            function ($c) {
                return new $c['dispatcher_class']($c);
            }
        );

        /*
         * Navigation
         */
        $container['navigation'] = $container->share(
            function ($c) {
                $navigation = new Container($c['user']);
                $navigation->loadFile($c['config']['navigation.data']);
                $navigation->setUri($c['dispatcher']->getUri());
                return $navigation;
            }
        );

        /*
         * Command factory
         */
        $container['command'] = $container->share(
            function ($c) {
                return new Factory($c['config']);
            }
        );

        /*
         * Intl
         */
        $container['intl_class'] = '\NETWAYS\Intl\Gettext';

        $container['intl'] = $container->share(
            function ($c) {
                $gettextController = new $c['intl_class']();

                $gettextController->setLocale($c['user']->getLocale());

                $gettextController->addDomain(
                    $c['config']['locale.domain'],
                    $c['config']['locale.path']
                );

                $gettextController->setDefaultDomain($c['config']['locale.domain']);

                return $gettextController;
            }
        );

        // Dummy call to initialize internationalization
        $container['intl'];

        // --------------------------------------------------------------------
        // Factories
        // --------------------------------------------------------------------

        // Application specific models

        $container['hostData'] = function ($c) {
            $hostData = new HostData($c);

            /*
             * Registering default attribute handler
             *
             * Add service ping to every service
             */
            $hostData->appendHandlerToChain(new DefaultAttributes($c));

            /*
             * Thomas krenn specific attributes
             *
             * Appends customfields to services to fit IPMI and SNMP checks
             * Changes notification templates if a service needs reporting
             * to Thomas Krenn
             */
            $hostData->appendHandlerToChain(new ThomasKrennAttributes($c));

            return $hostData;
        };

        $container['serviceData'] = function ($c) {
            $serviceData = new ServiceData($c);

            /*
             * Adds icinga templates to services if notification is needed
             */
            $serviceData->appendHandlerToChain(new ThomasKrennNotification($c));
            return $serviceData;
        };

        $container['serviceCatalogue'] = $container->share(
            function ($c) {

                /** @var $config \NETWAYS\Common\Config */
                $config = $c['config'];

                $catalogue = new Services();

                $jsonData = new JsonFiles();
                $jsonData->setCacheInterface($c['cache'], 'tkmon.catalogue.services');

                $simpleTranslator = new SimpleTranslator($c['user']->getLocale());
                $jsonData->setTranslator($simpleTranslator);

                // Add directory of json files to stack
                $dir = $config['icinga.catalogue.services.json.dir'];
                $jsonData->addDir($dir);

                $catalogue->appendHandlerToChain($jsonData);

                $catalogue->makeReady();

                return $catalogue;
            }
        );

        $container['logger']->debug('Bootstrap complete, do request');

        echo $container['dispatcher']->dispatchRequest();
    }
}
