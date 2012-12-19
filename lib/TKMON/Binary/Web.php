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
 * @copyright 2012-2013 NETWAYS GmbH <info@netways.de>
 */

namespace TKMON\Binary;

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

        // Be brave my friend!
        ini_set('display_errors', true);
        ini_set('error_reporting', E_ALL);

        require $libdir . DIRECTORY_SEPARATOR . 'Pimple' . DIRECTORY_SEPARATOR . 'Pimple.php';

        require $libdir . DIRECTORY_SEPARATOR . 'NETWAYS' . DIRECTORY_SEPARATOR . 'Common'
            . DIRECTORY_SEPARATOR . 'ClassLoader.php';

        require $libdir . DIRECTORY_SEPARATOR . 'Twig' . DIRECTORY_SEPARATOR . 'lib'
            . DIRECTORY_SEPARATOR . 'Twig' . DIRECTORY_SEPARATOR . 'Autoloader.php';

        $container = new \Pimple();

        // Loader for NETWAYS
        $loader = new \NETWAYS\Common\ClassLoader('NETWAYS', $libdir);
        $loader->register();

        // Loader for TKMON
        $loader = new \NETWAYS\Common\ClassLoader('TKMON', $libdir);
        $loader->register();

        // Loader for Twig
        \Twig_Autoloader::register();

        // -------------------------------------------------------------------------------------------------------------
        // Build our environment to deliver content to the world
        // -------------------------------------------------------------------------------------------------------------

        /*
         * Paths environment
         */
        $container['lib_dir'] = $libdir;
        $container['root_dir'] = dirname($libdir);
        $container['etc_dir'] = $container['root_dir'] . DIRECTORY_SEPARATOR . 'etc';
        $container['share_dir'] = $container['root_dir'] . DIRECTORY_SEPARATOR . 'share';

        /*
         * Cgi Params
         */
        $container['params_class'] = 'NETWAYS\Http\CgiParams';
        $container['params'] = $container->share(function ($c) {
            return new $c['params_class']();
        });

        /**
         * Configuration object
         */
        $container['config_class'] = 'NETWAYS\Common\Config';
        $container['config'] = $container->share(function ($c) {
            $params = $c['params'];

            $config = new $c['config_class'];

            // Path settings
            $config->set('core.root_dir', $c['root_dir']);
            $config->set('core.lib_dir', $c['lib_dir']);
            $config->set('core.etc_dir', $c['etc_dir']);
            $config->set('core.share_dir', $c['share_dir']);
            $config->set('core.template_dir', '{core.share_dir}/templates');
            $config->set('core.var_dir', '{core.root_dir}/var');
            $config->set('core.cache_dir', '{core.root_dir}/var/cache');

            // Web settings
            $filename = basename($params->getParameter('SCRIPT_FILENAME', null, 'header'));
            $path = str_replace($filename, '', $params->getParameter('SCRIPT_NAME', null, 'header'));

            $config->set('web.path', $path);
            $config->set('web.script', $path . $filename);
            $config->set('web.img_path', '{web.path}/img');
            $config->set('web.port', $params->getParameter('SERVER_PORT', null, 'header'));
            $config->set('web.domain', $params->getParameter('SERVER_NAME', null, 'header'));
            $config->set('web.https', false); // TODO: This should be detected

            $config->loadFile($c['etc_dir'] . DIRECTORY_SEPARATOR . 'config.json');
            return $config;
        });

        /*
         * Template engine
         */
        $container['template_loader'] = $container->share(function ($c) {
            return new \Twig_Loader_Filesystem($c['config']->get('core.template_dir'));
        });

        $container['template'] = $container->share(function ($c) {
            $twig = new \Twig_Environment($c['template_loader'], array(// 'cache' => $c['config']->get('core.cache_dir')
            ));

            $twig->addExtension(new \TKMON\Twig\Extension($c));

            return $twig;
        });

        /*
         * Database
         */
        $container['db_class'] = '\PDO';

        $container['db'] = $container->share(function ($c) {
            $config = $c['config'];

            $importer = new \TKMON\Model\Database\Importer();
            $importer->setDatabase($config['db.file']);
            $importer->setSchema($config['db.schema']);

            if (!$importer->databaseExists()) {
                $importer->createDefaultDatabase();
            }

            $dbo =  new $c['db_class']($config->get('db.dsn'), null, null,
                array(
                    \PDO::ATTR_PERSISTENT => true,
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_CASE => \PDO::CASE_LOWER,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                ));

            $dbo->exec('PRAGMA temp_store=MEMORY; PRAGMA journal_mode=MEMORY;');

            // Load additional settings from database
            $pdoLoader = new \NETWAYS\Common\Config\PDOLoader($dbo);
            $pdoLoader->setTable('config');
            $pdoLoader->setKeyColumn('name');
            $pdoLoader->setValueColumn('value');

            $c['config']->load($pdoLoader);

            // Configure persister to write data back
            $pdoPersister = new \NETWAYS\Common\Config\PDOPersister($dbo);
            $pdoPersister->setTable('config');
            $pdoPersister->setKeyColumn('name');
            $pdoPersister->setValueColumn('value');

            $c['config']->setPersister($pdoPersister);

            return $dbo;
        });

        // Trigger the database object to have it ready imported
        $container['db'];

        /*
         * Session
         */
        $container['session_class'] = 'NETWAYS\Http\Session';
        $container['session'] = $container->share(function ($c) {
            $config = $c['config'];

            $session = new $c['session_class']();
            $session->setName($config->get('session.name'));
            $session->setLifetime($config->get('session.lifetime'));
            $session->setDomain($config->get('web.domain'));
            $session->setIsSecured($config->get('web.https'));
            $session->setPath($config->get('web.path'));

            $session->start();

            return $session;
        });

        /*
         * User
         */
        $container['user_class'] = '\TKMON\Model\User';
        $container['user'] = $container->share(function ($c) {
            $user = new \TKMON\Model\User($c);
            $user->initialize();
            return $user;
        });

        /*
         * Dispatcher
         */
        $container['dispatcher_class'] = '\TKMON\Mvc\Dispatcher';
        $container['dispatcher'] = $container->share(function ($c) {
            return new $c['dispatcher_class']($c);
        });

        echo $container['dispatcher']->dispatchRequest();
    }

}