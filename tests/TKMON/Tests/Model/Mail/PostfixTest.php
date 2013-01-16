<?php

class PostfixTest extends PHPUnit_Framework_TestCase
{

    private static $container;
    private static $dataPath;
    private static $configFile = '/tmp/tkmon-postfix-main.cf';

    public static function setUpBeforeClass()
    {
        self::$container = new \TKMON\Test\Container();

        self::$dataPath = dirname(dirname(dirname(__DIR__)));
    }

    public function testLoad()
    {
        $testConfig = self::$dataPath. '/Data/Postfix/test1-main.cf';
        $this->assertTrue(file_exists($testConfig));
        copy($testConfig, self::$configFile);
        $this->assertTrue(file_exists(self::$configFile));

        $postfixConfig = new \TKMON\Model\Mail\Postfix(self::$container);
        $postfixConfig->setConfigFile(self::$configFile);
        $postfixConfig->load();

        $this->assertCount(38, (array)$postfixConfig);

        $postfixConfig->setRelayHost('192.168.10.10');
        $postfixConfig->write();

        $testConfigEquals = self::$dataPath. '/Data/Postfix/test2-main.cf';
        $this->assertFileEquals($testConfigEquals, self::$configFile);

        $this->assertTrue(unlink(self::$configFile));
    }

    /**
     * @expectedException \TKMON\Exception\ModelException
     */
    public function testEmptyWrite()
    {
        $postfixConfig = new \TKMON\Model\Mail\Postfix(self::$container);
        $postfixConfig->setConfigFile('/tmp/tkmon-does-not-exist-222.cf');
        $postfixConfig->write();
    }

    /**
     * @expectedException \TKMON\Exception\ModelException
     */
    public function testNotExistingLoad()
    {
        $postfixConfig = new \TKMON\Model\Mail\Postfix(self::$container);
        $postfixConfig->setConfigFile('/tmp/tkmon-does-not-exist-111.cf');
        $postfixConfig->load();
    }

}
