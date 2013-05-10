<?php

namespace TKMON\Tests\Model\ThomasKrenn;

use TKMON\Model\ThomasKrenn\RestInterface;

class RestInterfaceTest extends \PHPUnit_Framework_TestCase {

    private static $container;

    private static $authKey = ''; // Security data

    private static $serialNumber = ''; // Security data

    public static function setUpBeforeClass()
    {
        self::$container = new \TKMON\Test\Container();
    }

    /**
     * @group thomaskrenn
     */
    public function testDataObjects1()
    {
        $this->markTestSkipped('Please configure data');

        $restInterface = new RestInterface(self::$container);
        $restInterface->setAuthKey(self::$authKey);
        $restInterface->setLang('de');

        $object = $restInterface->getProductObject(self::$serialNumber);

        $this->assertObjectHasAttribute('productId', $object);
        $this->assertObjectHasAttribute('productLink', $object);

        $detail = $restInterface->getProductDetail($object->productId);

        $this->assertObjectHasAttribute('key', $detail);
        $this->assertObjectHasAttribute('title', $detail);
        $this->assertObjectHasAttribute('wiki_link', $detail);
    }

    /**
     * @group thomaskrenn
     */
    public function testProduct1()
    {
        $this->markTestSkipped('Please configure data');

        $restInterface = new RestInterface(self::$container);
        $restInterface->setAuthKey(self::$authKey);
        $restInterface->setLang('de');
        $this->assertEquals(9961, $restInterface->getProductIdForSerial(self::$serialNumber));
    }

    /**
     * @group thomaskrenn
     */
    public function testWikiLink1()
    {
        $this->markTestSkipped('Please configure data');

        $restInterface = new RestInterface(self::$container);
        $restInterface->setAuthKey(self::$authKey);
        $restInterface->setLang('de');
        $this->assertEquals('http://www.thomas-krenn.com/de/wiki/Low_Energy_Server', $restInterface->getWikiLinkForSerial(self::$serialNumber));

        $restInterface->setLang('en');
        $this->assertEquals('http://www.thomas-krenn.com/en/wiki/Low_Energy_Server', $restInterface->getWikiLinkForSerial(self::$serialNumber));
    }
}