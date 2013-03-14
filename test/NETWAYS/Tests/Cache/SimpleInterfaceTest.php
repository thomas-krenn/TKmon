<?php

namespace NETWAYS\Tests\Cache;

class SimpleInterfaceTest extends \PHPUnit_Framework_TestCase
{

    public function testInterface1()
    {
        $provider = new \NETWAYS\Cache\Provider\Local();
        $cache = new \NETWAYS\Cache\Manager($provider);

        $obj = new \stdClass();
        $objId = spl_object_hash($obj);

        $this->assertFalse($cache->hasItem('laola'));

        $cache->storeItem($obj);

        $retrieved = $cache->retrieveItem($objId);

        $testId = spl_object_hash($retrieved);

        $this->assertEquals($objId, $testId);

        $this->assertEquals($cache->getProvider(), $provider);
    }

    public function testInterface2()
    {
        $provider = new \NETWAYS\Cache\Provider\Local();
        $cache = new \NETWAYS\Cache\Manager($provider);

        $obj = new \stdClass();
        $obj->test = "test1";

        $objId = spl_object_hash($obj);

        $cache[$objId] = $obj;

        $this->assertEquals($obj, $cache->retrieveItem($objId));

        $this->assertEquals($objId, spl_object_hash($cache[$objId]));

        $this->assertTrue(isset($cache[$objId]));

        unset($cache[$objId]);

        $this->assertFalse(isset($cache[$objId]));
    }

    /**
     * @expectedException \NETWAYS\Cache\Exception\OperationErrorException
     * @expectedExceptionMessage $item is scalar, please provide an identifier
     */
    public function testInterface3()
    {
        $provider = new \NETWAYS\Cache\Provider\Local();
        $cache = new \NETWAYS\Cache\Manager($provider);
        $cache->storeItem('laola');
    }

}
