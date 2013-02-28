<?php

namespace TKMON\Tests\Mvc\Output;

class JsonTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $data = array(
            "test1" => "oka",
            "test2" => "okb"
        );

        $equal = json_encode($data);

        $json = new \TKMON\Mvc\Output\Json();
        $json->setAll($data);

        $this->assertEquals($equal, (string)$json);
        $this->assertEquals($equal, (string)$json->toString());
        $this->assertTrue($data === $json->getData());
    }
}
