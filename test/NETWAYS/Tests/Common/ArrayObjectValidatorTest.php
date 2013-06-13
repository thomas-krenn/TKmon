<?php

namespace NETWAYS\Tests\Common;

use NETWAYS\Common\ArrayObject;
use NETWAYS\Common\ArrayObjectValidator;
use NETWAYS\Common\ValidatorObject;

class ArrayObjectValidatorTest extends \PHPUnit_Framework_TestCase
{

    public function testFloat()
    {
        $object = new ArrayObject(array(
            'float1' => 0.12334,
            'float2' => (float)2
        ));

        $validator = new ArrayObjectValidator();
        $validator->addValidator('float1', 'float', FILTER_VALIDATE_FLOAT);
        $validator->addValidator('float2', 'float', FILTER_VALIDATE_FLOAT);

        $validator->throwOnErrors(true);
        $this->assertTrue($validator->validateArrayObject($object));
    }

    public function testIp()
    {
        $object = new ArrayObject(array(
            'ip1' => '10.10.10.10',
            'ip2' => '290.0.0.0'
        ));

        $validator = new ArrayObjectValidator();
        $validator->addValidator('ip1', 'ip', FILTER_VALIDATE_IP);
        $validator->addValidator('ip2', 'ip', FILTER_VALIDATE_IP);

        $validator->throwOnErrors(false);
        $this->assertFalse($validator->validateArrayObject($object));
    }

    public function testInt()
    {
        $object = new ArrayObject(array(
            'int1' => 1,
            'int2' => 2
        ));

        $validator = new ArrayObjectValidator();
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
        $object = new ArrayObject(array(
            'int1' => 1,
            'int2' => 2
        ));

        $validator = new ArrayObjectValidator();
        $validator->addValidator('int1', 'Integer', FILTER_VALIDATE_INT);
        $validator->addValidator('int1', 'Integer between 20 and 1000', FILTER_VALIDATE_INT, null, array(
            'min_range' => 20,
            'max_range' => 1000
        ));

        $validator->validateArrayObject($object);
    }

    /**
     * @expectedException \NETWAYS\Common\Exception\ValidatorException
     * @expectedExceptionMessage Validation of field field2 failed. (mandatory)
     */
    public function testMandarory1()
    {
        $object = new ArrayObject(array(
            'field1' => 'AAA',
            'field2' => null // FAIL
        ));

        $validator = new ArrayObjectValidator();

        $validator->addValidator(
            'field1',
            'mandatory',
            ArrayObjectValidator::VALIDATE_MANDATORY
        );

        $validator->addValidator(
            'field2',
            'mandatory',
            ArrayObjectValidator::VALIDATE_MANDATORY
        );

        $validator->validateArrayObject($object);
    }

    /**
     * @expectedException \NETWAYS\Common\Exception\ValidatorException
     * @expectedExceptionMessage Validation of field not_exist1 failed (Must be present). This field is mandatory
     */
    public function testMandatory2()
    {
        $object = new ArrayObject(array());

        $validator = new ArrayObjectValidator();

        $vnm = new ValidatorObject();
        $vnm->setField('not_exist123');
        $vnm->setType(ValidatorObject::VALIDATE_MANDATORY); // REGEX
        $vnm->setMandatory(false);

        $validator->addValidatorObject($vnm); // Does nothing

        $validator->addValidator(
            'not_exist1',
            'Must be present',
            ValidatorObject::VALIDATE_MANDATORY
        );

        $validator->validateArrayObject($object);
    }

    /**
     * @expectedException NETWAYS\Common\Exception\ValidatorException
     * @expectedExceptionMessage Validation of field ip2 failed. (public ip)
     */
    public function testFlags()
    {
        $object = new ArrayObject(array(
            'ip1' => '193.17.26.2',
            'ip2' => '192.168.10.1' // FAIL
        ));

        $validator = new ArrayObjectValidator();

        $validator->addValidator('ip1', 'public ip', FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE);
        $validator->addValidator('ip2', 'public ip', FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE);

        $validator->validateArrayObject($object);
    }

    public function testMiscCreations1()
    {
        $o = new ArrayObject(array(
            'f1' => 123,
            'f2' => 'asd',
            'f3' => 123.234
        ));

        $validator = new ArrayObjectValidator();

        // *** I ***
        $validator->addValidator(
            'f1',
            'Integer',
            FILTER_VALIDATE_INT
        );

        // *** II ***
        $f2 = new ValidatorObject();
        $f2->setField('f2');
        $f2->setHumanDescription('String');
        $f2->setType(ValidatorObject::VALIDATE_MANDATORY); // Same as a string: SOME MUST BE PRESET
        $validator->addValidatorObject($f2);

        // *** III ***
        $f3 = ValidatorObject::create(
            'f3',
            'Double',
            FILTER_VALIDATE_FLOAT
        );

        $validator->addValidatorObject($f3);

        $validator->validateArrayObject($o); // NOTHING SHOULD HAPPEN!

        $this->assertInstanceOf('\NETWAYS\Common\ValidatorObject', $validator['f1']);
        $this->assertInstanceOf('\NETWAYS\Common\ValidatorObject', $validator['f2']);
        $this->assertInstanceOf('\NETWAYS\Common\ValidatorObject', $validator['f3']);

        $this->assertCount(3, $validator);
    }

    /**
     * @expectedException NETWAYS\Common\Exception\ValidatorException
     * @expectedExceptionMessage Validation of field ip2 failed. (IP)
     */
    public function testMandatory3()
    {
        $o = new ArrayObject(array(
            'ip1' => '',
            'ip2' => '10.10.10.300'
        ));

        $validator = new ArrayObjectValidator();

        $validator->addValidatorObject(ValidatorObject::create('ip1', 'IP', FILTER_VALIDATE_IP, null, array(), false));
        $validator->addValidatorObject(ValidatorObject::create('ip2', 'IP', FILTER_VALIDATE_IP, null, array(), false));
        $validator->validateArrayObject($o);
    }
}
