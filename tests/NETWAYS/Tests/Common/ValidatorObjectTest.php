<?php

namespace NETWAYS\Tests\Common;

class ValidatorObjectTest extends \PHPUnit_Framework_TestCase
{

    public function testGetterAndSetters()
    {
        $data = new \NETWAYS\Common\ArrayObject();
        $data['test1'] = 0xf;

        $o = \NETWAYS\Common\ValidatorObject::Create(
            'test1',
            'test2',
            FILTER_VALIDATE_INT,
            FILTER_FLAG_ALLOW_HEX,
            array(
                'min_range' => 15,
                'max_range' => 15
            )
        );

        $o->addOption('test1', true);
        $o->addOption('test2', true);
        $o->addOption('test3', true);
        $o->addOption('test4', true);


        $validator = new \NETWAYS\Common\ArrayObjectValidator();
        $validator->addValidatorObject($o);
        $validator->validateArrayObject($data); // Nothing should happen

        $this->assertEquals('test1', $o->getField());
        $this->assertEquals('test2', $o->getHumanDescription());
        $this->assertEquals(FILTER_VALIDATE_INT, $o->getType());
        $this->assertEquals(FILTER_FLAG_ALLOW_HEX, $o->getFlags());
        $this->assertEquals(FILTER_VALIDATE_INT, $o->getOrigType());

        $o->removeOption('test3');
        $o->removeOption('test1');

        $this->assertTrue($o->optionExists('test2'));
        $this->assertFalse($o->optionExists('test1'));

        $this->assertEquals(
            array(
                'min_range' => 15,
                'max_range' => 15,
                'test2' => true,
                'test4' => true
            ),
            $o->getOptions()
        );
    }

}
