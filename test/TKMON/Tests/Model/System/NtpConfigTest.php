<?php

namespace TKMON\Tests\Model\System;

class NtpConfigTest extends \PHPUnit_Framework_TestCase
{

    private static $container;
    private static $dataPath;
    private static $targetFile = '/tmp/tkmon-ntpconfig.txt';

    public static function setUpBeforeClass()
    {
        self::$container = new \TKMON\Test\Container();
        self::$dataPath = dirname(dirname(dirname(__DIR__)));
    }

    public function testMax1()
    {
        $ntpConfig = new \TKMON\Model\System\NtpConfiguration(self::$container);
        $ntpConfig->setConfigFile(self::$targetFile);
        $ntpConfig->setMaxServers(3);

        $ntpConfig->addNtpServer('XXX1');
        $ntpConfig->addNtpServer('XXX1');
        $ntpConfig->addNtpServer('XXX1');

        $this->assertCount(3, $ntpConfig->getNtpServers());
    }

    /**
     * @expectedException \TKMON\Exception\ModelException
     */
    public function testMax2()
    {
        $ntpConfig = new \TKMON\Model\System\NtpConfiguration(self::$container);
        $ntpConfig->setConfigFile(self::$targetFile);
        $ntpConfig->setMaxServers(3);

        $ntpConfig->addNtpServer('XXX1');
        $ntpConfig->addNtpServer('XXX1');
        $ntpConfig->addNtpServer('XXX1');
        $ntpConfig->addNtpServer('XXX1');

    }

    public function testRead()
    {
        $file = self::$dataPath. '/Data/NtpConfig/config1.txt';

        copy($file, self::$targetFile);
        $this->assertTrue(file_exists(self::$targetFile));


        $ntpConfig = new \TKMON\Model\System\NtpConfiguration(self::$container);
        $ntpConfig->setConfigFile(self::$targetFile);
        $ntpConfig->setMaxServers(3);
        $ntpConfig->load();

        $ntpConfig->write();

        $this->assertContains(
            '# ###TKMON### NTP server added by TKMON\\Model\\System\\TKMON\Model\\System\\NtpConfiguration'. PHP_EOL,
            file_get_contents(self::$targetFile)
        );

        $this->assertTrue(unlink(self::$targetFile));
    }

    public function testPurge()
    {
        $file = self::$dataPath. '/Data/NtpConfig/config1.txt';

        copy($file, self::$targetFile);
        $this->assertTrue(file_exists(self::$targetFile));

        $ntpConfig = new \TKMON\Model\System\NtpConfiguration(self::$container);
        $ntpConfig->setConfigFile(self::$targetFile);
        $ntpConfig->load();

        $ntpConfig->purgeServers();

        $this->assertCount(0, $ntpConfig->getNtpServers());

        $ntpConfig->write();

        $this->assertNotContains(
            '# ###TKMON### NTP server added by TKMON\\Model\\System\\TKMON\Model\\System\\NtpConfiguration'. PHP_EOL,
            file_get_contents(self::$targetFile)
        );

        $this->assertTrue(unlink(self::$targetFile));
    }

}
