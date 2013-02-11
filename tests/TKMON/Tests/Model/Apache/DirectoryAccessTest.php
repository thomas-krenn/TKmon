<?php

namespace TKMON\Tests\Model\Apache;

class DirectoryAccessTest extends \PHPUnit_Framework_TestCase
{

    private static $container;
    private static $dataPath;
    private static $configFile = '/tmp/tkmon-apache-directory.conf';
    private static $sourceFile;

    public static function setUpBeforeClass()
    {
        self::$container = new \TKMON\Test\Container();
        self::$dataPath = dirname(dirname(dirname(__DIR__))). '/Data/Apache';
        self::$sourceFile = self::$dataPath. '/httpd-config1.conf';
    }

    protected function setUp()
    {
        copy(self::$sourceFile, self::$configFile);
        parent::setUp();
    }

    protected function tearDown()
    {
        unlink(self::$configFile);
        parent::tearDown();
    }


    public function testAccess1()
    {
        // If the test works
        $this->assertFileExists(self::$configFile);

        $da = new \TKMON\Model\Apache\DirectoryAccess(self::$container);
        $da->setFile(self::$configFile);
        $da->load();
        $da->allowLocalhostOnly();
        $da->write();

        $this->assertFileEquals(self::$dataPath. '/httpd-config1-test1.conf', $da->getFile());
    }

    public function testAccess2()
    {
        // If the test works
        $this->assertFileExists(self::$configFile);

        $da = new \TKMON\Model\Apache\DirectoryAccess(self::$container);
        $da->setFile(self::$configFile);
        $da->load();
        $da->allowAll();
        $da->write();

        $this->assertFileEquals(self::$dataPath. '/httpd-config1-test2.conf', $da->getFile());
    }

    public function testAccess3()
    {
        // If the test works
        $this->assertFileExists(self::$configFile);

        $da = new \TKMON\Model\Apache\DirectoryAccess(self::$container);
        $da->setFile(self::$configFile);
        $da->load();
        $da->setOrder(\TKMON\Model\Apache\DirectoryAccess::ORDER_DENY_ALLOW);
        $da->setAllowFrom('10.17.0.0/16');
        $da->write();

        $this->assertFileEquals(self::$dataPath. '/httpd-config1-test3.conf', $da->getFile());
    }

    /**
     * @expectedException \TKMON\Exception\ModelException
     * @expectedExceptionMessage Config file does not exist
     */
    public function testAccessError1()
    {
        $da = new \TKMON\Model\Apache\DirectoryAccess(self::$container);
        $da->setFile('/does/not/exists');
        $da->load();
    }

    /**
     * @expectedException \TKMON\Exception\ModelException
     * @expectedExceptionMessage Config file does not exist
     */
    public function testAccessError2()
    {
        $da = new \TKMON\Model\Apache\DirectoryAccess(self::$container);
        $da->setFile(self::$configFile);
        $da->load();
        $da->setFile('/does/not/exists');
        $da->write();
    }

    /**
     * @expectedException \TKMON\Exception\ModelException
     * @expectedExceptionMessage No data loaded before
     */
    public function testAccessError3()
    {
        $da = new \TKMON\Model\Apache\DirectoryAccess(self::$container);
        $da->setFile(self::$configFile);
        $da->write();
    }

    public function testRead1()
    {
        $da = new \TKMON\Model\Apache\DirectoryAccess(self::$container);
        $da->setFile(self::$dataPath. '/httpd-config2.conf');
        $da->load();

        $this->assertEquals('Deny,Allow', $da->getOrder());
        $this->assertEquals('123.123.123.1/32', $da->getAllowFrom());
    }

    public function testSetting()
    {
        $da = new \TKMON\Model\Apache\DirectoryAccess(self::$container);

        // Auto DI configuration
        $this->assertEquals('/test/apache/config', $da->getFile());

        $da->setFile(self::$dataPath. '/httpd-config2.conf');
        $da->load();

        $this->assertFalse($da->publicAccess());

        $da->setAllowFrom(\TKMON\Model\Apache\DirectoryAccess::FROM_ALL);

        $this->assertFalse($da->publicAccess());

        $da->setOrder(\TKMON\Model\Apache\DirectoryAccess::ORDER_ALLOW_DENY);

        $this->assertFalse($da->publicAccess());

        $da->setDenyFrom(\TKMON\Model\Apache\DirectoryAccess::FROM_NULL); // DROP FLAG

        $this->assertTrue($da->publicAccess());
    }
}
