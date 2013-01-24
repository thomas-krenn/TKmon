<?php

namespace ICINGA\Tests\Object;

class HostServiceTest extends \PHPUnit_Framework_TestCase
{

    private static $dataPath;

    public static function setUpBeforeClass()
    {
        self::$dataPath = dirname(dirname(__DIR__)). DIRECTORY_SEPARATOR. 'Data';
    }


    public function testCreate1()
    {

        $testFile = self::$dataPath. DIRECTORY_SEPARATOR. 'config1.txt';
        $this->assertFileExists($testFile);

        $host = new \ICINGA\Object\Host();
        $host->hostName = 'srv1.localdomain.local';
        $host->displayName = 'Test Server 1';
        $host->address = '127.0.0.1';

        $service1 = new \ICINGA\Object\Service();
        $service1->serviceDescription = 'linux-disk';
        $service1->checkCommand = 'check-disk|12%|12%';

        $host->addService($service1);

        $service2 = new \ICINGA\Object\Service();
        $service2->serviceDescription = 'linux-procs';
        $service2->checkCommand = 'check-procs|100|200';

        $host->addService($service2);

        $this->assertEquals(file_get_contents($testFile), (string)$host);
    }

}
