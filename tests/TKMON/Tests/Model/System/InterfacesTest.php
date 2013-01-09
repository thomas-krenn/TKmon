<?php

namespace TKMON\Tests\Model\System;

class InterfacesTest extends \PHPUnit_Framework_TestCase
{

    private static $container;
    private static $dataPath;
    private static $interfaceFile = '/tmp/tkmon-test-interfaces.txt';

    public static function setUpBeforeClass()
    {
        self::$container = new \TKMON\Test\Container();

        self::$dataPath = dirname(dirname(dirname(__DIR__)));
    }

    public static function tearDownAfterClass()
    {
        unlink(self::$interfaceFile);
    }

    public function testStep1()
    {

        $interfaceFile = new \TKMON\Model\System\Interfaces(self::$container);
        $interfaceFile->setInterfaceFile(self::$interfaceFile);
        $interfaceFile->setInterfaceName('eth0');
        $interfaceFile->setFlag('dhcp');
        $interfaceFile['dns-nameservers'] = '8.8.8.8 8.8.4.4';
        $interfaceFile->write();

        $file = self::$dataPath. '/Data/Interfaces/step1.txt';

        $this->assertFileEquals($file, $interfaceFile->getInterfaceFile());
    }

    public function testStep2()
    {

        $interfaceFile = new \TKMON\Model\System\Interfaces(self::$container);
        $interfaceFile->setInterfaceFile(self::$interfaceFile);
        $interfaceFile->setInterfaceName('eth1');
        $interfaceFile->load();
        $interfaceFile->setFlag('static');
        $interfaceFile['address'] = '1.2.3.4';
        $interfaceFile['netmask'] = '255.255.255.0';

        $interfaceFile->write();

        $file = self::$dataPath. '/Data/Interfaces/step2.txt';

        $this->assertFileEquals($file, $interfaceFile->getInterfaceFile());
    }

    public function testStep3()
    {

        $interfaceFile = new \TKMON\Model\System\Interfaces(self::$container);
        $interfaceFile->setInterfaceFile(self::$interfaceFile);

        $interfaceFile->setInterfaceName('eth1');
        $interfaceFile->load();
        $interfaceFile->purgeFlags();
        $interfaceFile->setFlag('dhcp');
        $interfaceFile->purgeOptions();
        $interfaceFile['dns-nameservers'] = '8.8.4.4';
        $interfaceFile->write();

        $file = self::$dataPath. '/Data/Interfaces/step3.txt';

        $this->assertFileEquals($file, $interfaceFile->getInterfaceFile());
    }

}
