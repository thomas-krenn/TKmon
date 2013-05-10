<?php

namespace TKMON\Tests\Model\ThomasKrenn;

use TKMON\Model\ThomasKrenn\Alert;
use TKMON\Model\ThomasKrenn\ContactInfo;

class AlertTest extends \PHPUnit_Framework_TestCase
{
    private static $container;
    private static $xmlFile = '/tmp/tkalert-test.xml';

    public static function setUpBeforeClass()
    {
        self::$container = new \TKMON\Test\Container();
    }

    /**
     * @group integration
     */
    public function testXmlCreation()
    {
        $contactInfo = new ContactInfo(self::$container);

        $alerter = new Alert(self::$container);
        $alerter->configureByContactInfo($contactInfo);
        $alerter->setType(Alert::TYPE_TEST);

        // Changing the process for testing
        $alerter->getProcessObject()->addNamedArgument('--dump-xml', self::$xmlFile);
        $alerter->commit();

        $this->assertFileExists(self::$xmlFile);
        $this->assertTrue(unlink(self::$xmlFile));
    }

    /**
     * @group integration
     */
    public function testMailSending()
    {
        $contactInfo = new ContactInfo(self::$container);

        $alerter = new Alert(self::$container);
        $alerter->configureByContactInfo($contactInfo);
        $alerter->setType(Alert::TYPE_HEARTBEAT);

        // Changing the process for testing
        // Send configured mail
        $alerter->prepare();
        $alerter->getProcessObject()->addNamedArgument('--override-target-mail', $contactInfo->getEmail());
        $alerter->getProcessObject()->addNamedArgument('--disable-gpg-encryption');

        $alerter->commit();
    }
}