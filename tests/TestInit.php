<?php
namespace NETWAYS;

final class TestKit {
    public static function init() {
        $s = DIRECTORY_SEPARATOR;
        $dir = dirname(dirname(__FILE__));
        $libdir = $dir. $s. 'lib';

        require_once $libdir. $s. 'NETWAYS'. $s. 'Common'. $s. 'ClassLoader.php';

        $classLoader = new \NETWAYS\Common\ClassLoader('NETWAYS', $libdir);
        $classLoader->register();

        $classLoader = new \NETWAYS\Common\ClassLoader('TKMON', $libdir);
        $classLoader->register();

        $classLoader = new \NETWAYS\Common\ClassLoader('', $libdir. DIRECTORY_SEPARATOR. 'Twig/lib');
        $classLoader->register();

        $classLoader = new \NETWAYS\Common\ClassLoader('', $libdir. DIRECTORY_SEPARATOR. 'Pimple');
        $classLoader->register();
    }
}

TestKit::init();