<?php

namespace TKMON\Binary;

/**
 * Executor class to run the web stack in a function scope
 * @package TKMON
 */
final class Web
{
    /**
     * Inits the framework, and output to the world
     */
    public static function run()
    {
        $dirname = dirname(__FILE__);
        $libdir = dirname(dirname($dirname));

        // Be brave my friend!
        ini_set('display_errors', true);
        ini_set('error_reporting', E_ALL);

        require $libdir. DIRECTORY_SEPARATOR. 'Pimple'. DIRECTORY_SEPARATOR. 'Pimple.php';

        require $libdir. DIRECTORY_SEPARATOR. 'NETWAYS'. DIRECTORY_SEPARATOR. 'Common'
            .DIRECTORY_SEPARATOR. 'ClassLoader.php';

        require $libdir. DIRECTORY_SEPARATOR. 'Twig'. DIRECTORY_SEPARATOR. 'lib'
            . DIRECTORY_SEPARATOR. 'Twig'. DIRECTORY_SEPARATOR. 'Autoloader.php';

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
        $container['etc_dir'] = $container['root_dir']. DIRECTORY_SEPARATOR. 'etc';
        $container['share_dir'] = $container['root_dir']. DIRECTORY_SEPARATOR. 'share';

        /*
         * Cgi Params
         */
        $container['params_class'] = 'NETWAYS\Http\CgiParams';
        $container['params'] = $container->share(function($c) {
            return new $c['params_class']();
        });

        /**
         * Configuration object
         */
        $container['config_class'] = 'NETWAYS\Common\Config';
        $container['config'] = $container->share(function($c) {
            $params = $c['params'];

            $config = new $c['config_class'];

            // Path settings
            $config->set('core.root_dir', $c['root_dir']);
            $config->set('core.lib_dir', $c['lib_dir']);
            $config->set('core.etc_dir', $c['etc_dir']);
            $config->set('core.share_dir', $c['share_dir']);
            $config->set('core.template_dir', '{core.share_dir}/templates');
            $config->set('core.cache_dir', '{core.root_dir}/var/cache');

            // Web settings
            $filename = basename($params->getParameter('SCRIPT_FILENAME', null, 'header'));
            $path = str_replace($filename, '', $params->getParameter('PHP_SELF', null, 'header'));

            $config->set('web.path', $path);
            $config->set('web.script', $path. $filename);
            $config->set('web.img_path', '{web.path}/img');
            $config->set('web.port', $params->getParameter('SERVER_PORT', null, 'header'));
            $config->set('web.domain', $params->getParameter('SERVER_NAME', null, 'header'));
            $config->set('web.https', false); // TODO: This should be detected

            $config->loadFile($c['etc_dir']. DIRECTORY_SEPARATOR. 'config.json');
            return $config;
        });

        /*
         * Template engine
         */
        $container['template_loader'] = $container->share(function($c) {
            return new \Twig_Loader_Filesystem($c['config']->get('core.template_dir'));
        });

        $container['template'] = $container->share(function($c) {
           $twig = new \Twig_Environment($c['template_loader'], array(
               // 'cache' => $c['config']->get('core.cache_dir')
           ));

            $twig->addExtension(new \TKMON\Twig\Extension($c));

            return $twig;
        });

        /*
         * Session
         */
        $container['session_class'] = 'NETWAYS\Http\Session';
        $container['session'] = $container->share(function($c) {
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
         * Dispatcher
         */
        $container['dispatcher_class'] = '\TKMON\Mvc\Dispatcher';
        $container['dispatcher'] = $container->share(function($c) {
            return new $c['dispatcher_class']($c);
        });

        echo $container['dispatcher']->dispatchRequest();
    }

}