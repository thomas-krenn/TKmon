<?php
namespace ICINGA\Tests\Base;

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

}
