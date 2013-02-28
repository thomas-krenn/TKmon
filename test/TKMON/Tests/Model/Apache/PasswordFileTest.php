<?php

namespace TKMON\Tests\Model\Apache;

class PasswordFileTest extends \PHPUnit_Framework_TestCase
{

    private static $container;
    private static $dataPath;
    private static $configFile = '/tmp/tkmon-htpasswd1.txt';

    public static function setUpBeforeClass()
    {
        self::$container = new \TKMON\Test\Container();
        self::$dataPath = dirname(dirname(dirname(__DIR__)));
    }

    public function testLoad()
    {
        $testFile1 = self::$dataPath. '/Data/Apache/htpasswd1.txt';

        copy($testFile1, self::$configFile);
        $this->assertTrue(file_exists(self::$configFile));

        $passwd = new \TKMON\Model\Apache\PasswordFile(self::$container);
        $passwd->setPasswordFile(self::$configFile);
        $passwd->load();

        $this->assertCount(3, $passwd);

        $passwd->addUser('user4', 'user4');

        $passwd->addUser('user5', 'user5');

        unset($passwd['user1']);

        $passwd->write();

        $haystack = file_get_contents(self::$configFile);

        $this->assertContains('user2:', $haystack);
        $this->assertContains('user5:', $haystack);
        $this->assertNotContains('user1:', $haystack);

        $this->assertTrue(unlink(self::$configFile));
    }

    /**
     * @expectedException TKMON\Exception\ModelException
     */
    public function testLoadError()
    {
        $passwd = new \TKMON\Model\Apache\PasswordFile(self::$container);
        $passwd->setPasswordFile('/tmp/does-not-exists-1231233.txt');
        $passwd->load();
    }

    /**
     * @expectedException TKMON\Exception\ModelException
     */
    public function testWriteError()
    {
        $passwd = new \TKMON\Model\Apache\PasswordFile(self::$container);
        $passwd->setPasswordFile('/tmp/does-not-exists-1231233.txt');
        $passwd->write();
    }

    public function testInterfaces()
    {
        $passwd = new \TKMON\Model\Apache\PasswordFile(self::$container);
        $this->assertInstanceOf('\Pimple', $passwd->getContainer());

        $container = new \Pimple();
        $contrainerId = spl_object_hash($container);

        $passwd->setContainer($container);
        $this->assertEquals($contrainerId, spl_object_hash($passwd->getContainer()));
    }
}

