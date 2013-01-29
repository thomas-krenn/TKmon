<?php

namespace NETWAYS\Tests\Common;

class ArrayObjectValidatorTest extends \PHPUnit_Framework_TestCase
{

    public function testFloat()
    {
        $object = new \NETWAYS\Common\ArrayObject(array(
            'float1' => 0.12334,
            'float2' => (float)2
        ));

        $validator = new \NETWAYS\Common\ArrayObjectValidator();
        $validator->addValidator('float1', 'float', FILTER_VALIDATE_FLOAT);
        $validator->addValidator('float2', 'float', FILTER_VALIDATE_FLOAT);

        $validator->throwOnErrors(true);
        $this->assertTrue($validator->validateArrayObject($object));
    }

    public function testIp()
    {
        $object = new \NETWAYS\Common\ArrayObject(array(
            'ip1' => '10.10.10.10',
            'ip2' => '290.0.0.0'
        ));

        $validator = new \NETWAYS\Common\ArrayObjectValidator();
        $validator->addValidator('ip1', 'ip', FILTER_VALIDATE_IP);
        $validator->addValidator('ip2', 'ip', FILTER_VALIDATE_IP);

        $validator->throwOnErrors(false);
        $this->assertFalse($validator->validateArrayObject($object));
    }

    public function testInt()
    {
        $object = new \NETWAYS\Common\ArrayObject(array(
            'int1' => 1,
            'int2' => 2
        ));

        $validator = new \NETWAYS\Common\ArrayObjectValidator();
        $validator->addValidator('int1', 'Integer', FILTER_VALIDATE_INT);
        $validator->addValidator('int1', 'Integer', FILTER_VALIDATE_INT, null, array(
            'min_range' => 1,
            'max_range' => 10
        ));

        $validator->validateArrayObject($object);
    }

    /**
     * @expectedException \NETWAYS\Common\Exception\ValidatorException
     */
    public function testIntFail()
    {
        $object = new \NETWAYS\Common\ArrayObject(array(
            'int1' => 1,
            'int2' => 2
        ));

        $validator = new \NETWAYS\Common\ArrayObjectValidator();
        $validator->addValidator('int1', 'Integer', FILTER_VALIDATE_INT);
        $validator->addValidator('int1', 'Integer between 20 and 1000', FILTER_VALIDATE_INT, null, array(
            'min_range' => 20,
            'max_range' => 1000
        ));

        $validator->validateArrayObject($object);
    }

    /**
     * @expectedException \NETWAYS\Common\Exception\ValidatorException
     * @expectedExceptionMessage Validation of field field2 fails. (mandatory)
     */
    public function testMandarory()
    {
        $object = new \NETWAYS\Common\ArrayObject(array(
            'field1' => 'AAA',
            'field2' => null // FAIL
        ));

        $validator = new \NETWAYS\Common\ArrayObjectValidator();

        $validator->addValidator(
            'field1',
            'mandatory',
            \NETWAYS\Common\ArrayObjectValidator::VALIDATE_MANDATORY
        );

        $validator->addValidator(
            'field2',
            'mandatory',
            \NETWAYS\Common\ArrayObjectValidator::VALIDATE_MANDATORY
        );

        $validator->validateArrayObject($object);
    }

    /**
     * @expectedException NETWAYS\Common\Exception\ValidatorException
     * @exoectedExceptionMessage Validation of field ip2 fails. (public ip)
     */
    public function testFlags()
    {
        $object = new \NETWAYS\Common\ArrayObject(array(
            'ip1' => '193.17.26.2',
            'ip2' => '192.168.10.1' // FAIL
        ));

        $validator = new \NETWAYS\Common\ArrayObjectValidator();

        $validator->addValidator('ip1', 'public ip', FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE);
        $validator->addValidator('ip2', 'public ip', FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE);

        $validator->validateArrayObject($object);
    }
}
