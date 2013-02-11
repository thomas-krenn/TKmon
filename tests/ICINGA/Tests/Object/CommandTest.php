<?php

namespace ICINGA\Tests\Object;

class CommandTest extends \PHPUnit_Framework_TestCase
{

    public function testArgumentInterface1()
    {
        $command = new \ICINGA\Object\Command();
        $command->setCommandName('check-tcp');

        $argument1 = new \ICINGA\Base\CommandArgument();
        $argument1->setValue(80);

        $command->setArguments(array($argument1));

        $this->assertEquals('check-tcp!80', $command->getCheckCommand());

        $this->assertEquals(
            array(
                $argument1
            ),
            $command->getArguments()
        );
    }

    public function testArgumentInterface2()
    {
        $command = new \ICINGA\Object\Command();
        $command->setCommandName('check-fake123');

        $command->addArgument(\ICINGA\Base\CommandArgument::create('aaa'));
        $command->addArgument(\ICINGA\Base\CommandArgument::create('bbb'));
        $command->addArgument(\ICINGA\Base\CommandArgument::create('ccc'));

        $command->setArgumentValue(2, 'xxx');
        $command->removeArgument(1);

        $this->assertCount(2, $command->getArguments());

        $this->assertEquals('check-fake123!aaa!xxx', $command->getCheckCommand());

        $command->purgeArguments();
        $this->assertCount(0, $command->getArguments());
    }

    /**
     * @expectedException ICINGA\Exception\AttributeException
     * @expectedExceptionMessage Argument not found with index: 3
     */
    public function testExceptions1()
    {
        $command = new \ICINGA\Object\Command();
        $command->setCommandName('check-fake123');

        $command->addArgument(\ICINGA\Base\CommandArgument::create('aaa'));
        $command->addArgument(\ICINGA\Base\CommandArgument::create('bbb'));
        $command->addArgument(\ICINGA\Base\CommandArgument::create('ccc'));

        $command->getArgument(3);
    }

    /**
     * @expectedException ICINGA\Exception\AttributeException
     * @expectedExceptionMessage Argument not found with index: 4
     */
    public function testExceptions2()
    {
        $command = new \ICINGA\Object\Command();
        $command->setCommandName('check-fake123');

        $command->addArgument(\ICINGA\Base\CommandArgument::create('aaa'));
        $command->addArgument(\ICINGA\Base\CommandArgument::create('bbb'));
        $command->addArgument(\ICINGA\Base\CommandArgument::create('ccc'));

        $command->setArgumentValue(0, 'asd');
        $command->setArgumentValue(4, 'asd');
    }

    /**
     * @expectedException ICINGA\Exception\AttributeException
     * @expectedExceptionMessage Argument not found with index: 12
     */
    public function testExceptions3()
    {
        $command = new \ICINGA\Object\Command();
        $command->setCommandName('check-fake123');

        $command->addArgument(\ICINGA\Base\CommandArgument::create('aaa'));
        $command->addArgument(\ICINGA\Base\CommandArgument::create('bbb'));
        $command->addArgument(\ICINGA\Base\CommandArgument::create('ccc'));

        $command->removeArgument(0);
        $command->removeArgument(12);
    }

    /**
     * @expectedException ICINGA\Exception\ConfigException
     * @expectedExceptionMessage $commandLine not set
     */
    public function testExceptions4()
    {
        $command = new \ICINGA\Object\Command();
        $command->setCommandName('check-dings');

        $test = $command->toString();
    }

    public function testIdentifier()
    {
        $command = new \ICINGA\Object\Command();
        $command->setCommandName('check-dings');
        $command->setCommandLine('/tmp/dings.sh');

        $command->assertObjectIsValid(); // nothing should happen

        $this->assertEquals('check-dings', $command->getObjectIdentifier());
    }
}
