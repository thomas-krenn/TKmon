<?php
namespace ICINGA\Tests\Base;

use ICINGA\Base\CommandArgument;

class CommandArgumentTest extends \PHPUnit_Framework_TestCase
{

    public function testInterface1()
    {
        $commandArgument = new \ICINGA\Base\CommandArgument();
        $commandArgument->setValue('AAA');
        $commandArgument->setArgument('BBB');
        $commandArgument->setDescription('CCC');
        $commandArgument->setLabel('DDD');
        $commandArgument->setValidation('EEE');

        $this->assertEquals('AAA', $commandArgument->getValue());
        $this->assertEquals('BBB', $commandArgument->getArgument());
        $this->assertEquals('CCC', $commandArgument->getDescription());
        $this->assertEquals('DDD', $commandArgument->getLabel());
        $this->assertEquals('EEE', $commandArgument->getValidation());
    }

    public function testInterface2()
    {
        $commandArgument = \ICINGA\Base\CommandArgument::create(
            'AAA',
            'BBB',
            'DDD',
            'CCC'
        );

        $this->assertEquals('AAA', $commandArgument->getValue());
        $this->assertEquals('BBB', $commandArgument->getArgument());
        $this->assertEquals('CCC', $commandArgument->getDescription());
        $this->assertEquals('DDD', $commandArgument->getLabel());
        $this->assertEquals('string', $commandArgument->getValidation());
    }

    public function testCommandType()
    {
        $commandArgument = CommandArgument::create(
            'aaa',
            'bbb',
            'ccc',
            'ddd',
            'eee',
            'fff'
        );

        $this->assertSame('fff', $commandArgument->getType());

        $commandArgument->setType('password');
        $this->assertSame('password', $commandArgument->getType());

        $voyager = new \stdClass();
        $voyager->type = 'checkbox';
        $voyager->value = 123123;

        $commandArgument2 = CommandArgument::createFromVoyager($voyager);
        $this->assertSame('checkbox', $commandArgument2->getType());
        $this->assertSame(123123, $commandArgument2->getValue());
    }

}
