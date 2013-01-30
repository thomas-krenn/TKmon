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
        $da->allowLocalhostOnly();

        $da->rewrite();

        $this->assertFileEquals(self::$dataPath. '/httpd-config1-test1.conf', $da->getFile());
    }

    public function testAccess2()
    {
        // If the test works
        $this->assertFileExists(self::$configFile);

        $da = new \TKMON\Model\Apache\DirectoryAccess(self::$container);
        $da->setFile(self::$configFile);
        $da->allowAll();

        $da->rewrite();

        $this->assertFileEquals(self::$dataPath. '/httpd-config1-test2.conf', $da->getFile());
    }

    public function testAccess3()
    {
        // If the test works
        $this->assertFileExists(self::$configFile);

        $da = new \TKMON\Model\Apache\DirectoryAccess(self::$container);
        $da->setFile(self::$configFile);

        $da->setOrder(\TKMON\Model\Apache\DirectoryAccess::ORDER_DENY_ALLOW);
        $da->setFrom('10.17.0.0/16');

        $da->rewrite();

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
        $da->rewrite(); // KA-BOOM
    }
}
