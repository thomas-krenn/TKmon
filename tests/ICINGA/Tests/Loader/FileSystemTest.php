<?php

namespace ICINGA\Tests\Loader;

class FileSystemTest extends \PHPUnit_Framework_TestCase
{

    private static $dataPath;
    private static $targetDir;

    public static function setUpBeforeClass()
    {
        self::$dataPath = dirname(dirname(__DIR__)). DIRECTORY_SEPARATOR. 'Data';
    }

    public function setUp()
    {
        // Prepare test
        self::$targetDir = '/tmp/icinga-config-test1';
        $dir = self::$dataPath. DIRECTORY_SEPARATOR. 'TestSource1';
        $this->assertTrue(file_exists($dir));

        // Prepare test II
        exec("/bin/cp -rf $dir ". self::$targetDir);
        $this->assertTrue(is_dir(self::$targetDir));
    }

    public function tearDown()
    {
        // Cleanup test
        exec("/bin/rm -rf ". self::$targetDir);
        $this->assertFalse(file_exists(self::$targetDir));
    }

    public function testLoad1()
    {
        $strategy = new \ICINGA\Loader\Strategy\HostServiceObjects();
        $loader = new \ICINGA\Loader\FileSystem();
        $loader->setStrategy($strategy);
        $loader->setPath(self::$targetDir);
        $loader->load();

        $this->assertCount(5, $loader);

        /** @var $host \ICINGA\Object\Host */
        $host = $loader['TEST1.1'];

        $this->assertCount(2, $host->getServices());

        $this->assertInstanceOf('\ICINGA\Object\Service', $host->getService('PING'));
        $this->assertInstanceOf('\ICINGA\Object\Service', $host->getService('PROCS'));

        $this->assertInstanceOf('\ICINGA\Object\Host', $host->getService('PING')->getHost());
        $this->assertInstanceOf('\ICINGA\Object\Host', $host->getService('PROCS')->getHost());

        $this->assertEquals('TEST1.1', $host->getService('PROCS')->getHost()->hostName);

        /** @var $host2 \ICINGA\Object\Host */
        $host2 = $loader['TEST2.1'];

        $this->assertTrue($host2->hasCustomVariables());
        $this->assertTrue($host2->getService('PROCS')->hasCustomVariables());
        $this->assertCount(2, $host2->getService('PROCS')->getCustomVariables());

        $this->assertEquals('BB', $host2->getCustomVariable('TEST2'));
        $this->assertEquals('CC', $host2->getService('PROCS')->getCustomVariable('TEST3'));


        $service = $host2->getService('PROCS');
        $this->assertTrue($host2->serviceExists($service));

        $service = $host2->getService('PING');
        $this->assertTrue($host2->serviceExists($service));

        $this->assertCount(2, $host2->getServices());
        $service = $host2->getService('PROCS');
        $host2->removeService($service);
        $this->assertCount(1, $host2->getServices());

        $host2->purgeService();
        $this->assertCount(0, $host2->getServices());
    }

    public function testLoad2()
    {
        $strategy = new \ICINGA\Loader\Strategy\SimpleObject();
        $loader = new \ICINGA\Loader\FileSystem();
        $loader->setStrategy($strategy);
        $loader->setPath(self::$targetDir);
        $loader->load();

        $this->assertCount(13, $loader);
        $this->assertTrue($loader->offsetExists('TEST1.1_PROCS'));
        $this->assertTrue($loader->offsetExists('TEST1.1_PING'));

        $this->assertEquals('host', $loader['TEST1.1']->getObjectName());
        $this->assertEquals('service', $loader['TEST2.1_PING']->getObjectName());

        $this->assertEquals('AA', $loader['TEST2.1']->getCustomVariable('TEST1'));
        $this->assertEquals('DD', $loader['TEST2.1_PROCS']->getCustomVariable('TEST4'));
    }

    public function testWrite1()
    {
        $strategy = new \ICINGA\Loader\Strategy\HostServiceObjects();
        $loader = new \ICINGA\Loader\FileSystem();
        $loader->setStrategy($strategy);
        $loader->setPath(self::$targetDir);
        $loader->load();

        $dir = '/tmp/icinga-output-test';
        exec("mkdir -p $dir");
        $this->assertTrue(file_exists($dir));

        $loader->setPath($dir);
        $loader->write();

        $testFile1 = '/tmp/icinga-output-test/TEST1.1.cfg';
        $testFile2 = '/tmp/icinga-output-test/TEST2.1.cfg';

        $this->assertTrue(file_exists($testFile1));
        $this->assertTrue(file_exists($testFile2));

        $testContent1 = file_get_contents($testFile1);
        $testContent2 = file_get_contents($testFile2);

        $this->assertContains('    service_description           PING'. PHP_EOL, $testContent1);
        $this->assertContains('    service_description           PROCS'. PHP_EOL, $testContent1);

        $this->assertContains('    _TEST3                        CC'. PHP_EOL, $testContent2);
        $this->assertContains('    _TEST4                        DD'. PHP_EOL, $testContent2);

        exec("/bin/rm -rf $dir");
        $this->assertFalse(is_file($dir));
    }

    /**
     * @expectedException \ICINGA\Exception\LoadException
     * @expectedExceptionMessage Could not load from any other that a directory
     */
    public function testLoadException()
    {
        $strategy = new \ICINGA\Loader\Strategy\HostServiceObjects();
        $loader = new \ICINGA\Loader\FileSystem();
        $loader->setStrategy($strategy);
        $loader->load();
    }

    /**
     * @expectedException ICINGA\Exception\ConfigException
     * @expectedExceptionMessage Strategy is not configured
     */
    public function testStrategyException()
    {
        $loader = new \ICINGA\Loader\FileSystem();
        $loader->setPath('/tmp');
        $loader->load();
    }
}
