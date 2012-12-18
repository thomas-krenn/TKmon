<?php

namespace NETWAYS\Tests\Common;

class ClassLoaderTest extends \PHPUnit_Framework_TestCase
{

    public function testNewObject()
    {
        $cl = new \NETWAYS\Common\ClassLoader('UNKNOWN', '/tmp');
        $this->assertEquals('UNKNOWN', $cl->getNamespace());
        $this->assertEquals('/tmp', $cl->getIncludePath());
    }

    public function testFileExtension()
    {
        $cl = new \NETWAYS\Common\ClassLoader('UNKNOWN', '/tmp');
        $this->assertEquals('.php', $cl->getFileExtension());
        $cl->setFileExtension('.tmp');
        $this->assertEquals('.tmp', $cl->getFileExtension());
    }

    public function testNamespaceSeparator()
    {
        $cl = new \NETWAYS\Common\ClassLoader('UNKNOWN', '/tmp');
        $this->assertEquals('\\', $cl->getNamespaceSeparator());
        $cl->setNamespaceSeparator('::');
        $this->assertEquals('::', $cl->getNamespaceSeparator());
    }

    public function testLoadClass()
    {
        $cl = new \NETWAYS\Common\ClassLoader('UNKNOWN', '/tmp');
        $re = $cl->loadClass('\NOT\EXIST\Laola');
        $this->assertFalse($re);
    }

    public function testRegister()
    {
        $cl = new \NETWAYS\Common\ClassLoader('UNKNOWN', '/tmp');
        $testCallback = array($cl, 'loadClass');

        $testArray = spl_autoload_functions();
        $this->assertFalse(in_array($testCallback, $testArray));

        $cl->register();
        $testArray = spl_autoload_functions();
        $this->assertTrue(in_array($testCallback, $testArray));

        $cl->unregister();
        $testArray = spl_autoload_functions();
        $this->assertFalse(in_array($testCallback, $testArray));
    }

}
