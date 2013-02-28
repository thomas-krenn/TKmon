<?php

namespace ICINGA\Tests\Object;

class ServiceCommandTest extends \PHPUnit_Framework_TestCase
{

    public function testString1()
    {
        $host = new \ICINGA\Object\Host();
        $host->hostName = 'test-host';
        $host->alias = 'Test Host';
        $host->address = '10.10.10.10';

        $service = new \ICINGA\Object\Service();
        $service->serviceDescription = 'net-ping';

        $command = new \ICINGA\Object\Command();
        $command->setCommandName('check-ping');

        $command->addArgument(\ICINGA\Base\CommandArgument::create('1000,70%'));
        $command->addArgument(\ICINGA\Base\CommandArgument::create('1200,100%'));

        $service->setHost($host);
        $service->setCommand($command);

        $testString = (string)$service;

        $this->assertContains('check_command                 check-ping!1000,70%!1200,100%', $testString);

        // Test lazy changing of values
        $command->setArgumentValue(0, '4000,10%');
        $command->setArgumentValue(1, '6000,50%');

        $this->assertCount(2, $command->getArguments());

        $this->assertInstanceOf('\ICINGA\Base\CommandArgument', $command->getArgument(0));
        $this->assertInstanceOf('\ICINGA\Base\CommandArgument', $command->getArgument(1));

        $this->assertEquals('check-ping!4000,10%!6000,50%', $command->getCheckCommand());

    }


}
