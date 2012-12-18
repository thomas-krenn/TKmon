<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mhein
 * Date: 17/12/12
 * Time: 21:51
 * To change this template use File | Settings | File Templates.
 */
class ArrayObjectTest extends \PHPUnit_Framework_TestCase
{

    public function testNewObject()
    {
        $o1 = new \NETWAYS\Common\ArrayObject();
        $this->assertCount(0, (array)$o1);

        $o2 = new \NETWAYS\Common\ArrayObject(array(1, 2, 3));
        $this->assertCount(3, (array)$o2);
    }

    public function testSetAll()
    {
        $o1 = new \NETWAYS\Common\ArrayObject();
        $o1->setAll(array(1, 2, 3, 4));
        $this->assertCount(4, (array)$o1);
    }

    public function testClearAll()
    {
        $o1 = new \NETWAYS\Common\ArrayObject();
        $o1->setAll(array(1, 2, 3, 4));
        $o1->clear();
        $this->assertCount(0, (array)$o1);
    }

    public function testGet()
    {
        $o1 = new \NETWAYS\Common\ArrayObject(array(
            'test1' => 'oka',
            'test2' => 'okb'
        ));

        $this->assertEquals('oka', $o1['test1']);
        $this->assertEquals('okb', $o1->get('test2'));

        $this->assertEquals('not_found', $o1->get('kkkk', 'not_found'));
    }

    public function testSet()
    {
        $o1 = new \NETWAYS\Common\ArrayObject();
        $o1->set('test1', 'oka');
        $o1['test2'] = 'okb';

        $this->assertEquals('oka', $o1['test1']);
        $this->assertEquals('okb', $o1->get('test2'));
    }

    public function testGetAll()
    {
        $o1 = new \NETWAYS\Common\ArrayObject();
        $o1->setAll(array(1, 2, 3));
        $this->assertEquals(array(1, 2, 3), $o1->getAll());
        $this->assertEquals(array(1, 2, 3), (array)$o1);
    }

}
