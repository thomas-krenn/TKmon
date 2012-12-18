<?php

namespace TKMON\Tests\Mvc\Output;

class SimpleStringTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $s = new \TKMON\Mvc\Output\SimpleString("test");

        $this->assertEquals('test', (string)$s);
        $this->assertEquals('test', $s->toString());
        $this->assertEquals('test', $s->getData());
    }
}
