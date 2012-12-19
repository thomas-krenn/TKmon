<?php

namespace TKMON\Tests\Mvc\Output;

class JsonResponseTest extends \PHPUnit_Framework_TestCase
{

    public function testCreate()
    {

        $test_data = array(
            'success' => false,
            'errors' => array(array(
                'message' => 'ok',
                'reftype' => 'server',
                'ref' => 'exception'
            )),
            'data' => array(array('ok'))
        );

        $equal = json_encode($test_data);

        $res = new \TKMON\Mvc\Output\JsonResponse();
        $e = new \Exception('ok');
        $res->addException($e);
        $res->setSuccess(false);
        $res->addData(array('ok'));

        $this->assertEquals($res->getData(), $test_data);
        $this->assertEquals($equal, (string)$res);
        $this->assertEquals($equal, $res->toString());
    }

}
