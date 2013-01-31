<?php

namespace NETWAYS\Tests\Chain;

require 'DummyHandler1.php';
require 'DummyReflectionHandler1.php';

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testChain1()
    {
        $manager = new \NETWAYS\Chain\Manager();

        $manager->appendHandlerToChain(new DummyHandler1(true));
        $manager->appendHandlerToChain(new DummyHandler1());
        $manager->appendHandlerToChain(new DummyHandler1());

        $command = new \NETWAYS\Chain\Command('fibunacci');
        $command['a'] = 1;
        $command['b'] = 1;

        try {
            $manager->processRequest($command);
        } catch (\NETWAYS\Chain\Exception\HandlerException $e) {
            $this->assertEquals(3, $command['a']);
            $this->assertEquals(5, $command['b']);

            $this->assertEquals('BOOM', $e->getMessage());
        }
    }

    public function testChain2()
    {
        $manager = new \NETWAYS\Chain\Manager();

        $manager->appendHandlerToChain(new DummyHandler1(true));
        $manager->appendHandlerToChain(new DummyHandler1());
        $manager->appendHandlerToChain(new DummyHandler1());

        $command = new \NETWAYS\Chain\Command('fibunacci');
        $command['a'] = 1;
        $command['b'] = 1;

        try {
            $manager->stopOnFirstHandlerException(true);
            $manager->processRequest($command);
        } catch (\NETWAYS\Chain\Exception\HandlerException $e) {
            $this->assertEquals(1, $command['a']);
            $this->assertEquals(2, $command['b']);

            $this->assertEquals('BOOM', $e->getMessage());
        }
    }

    public function testChain3()
    {
        $manager = new \NETWAYS\Chain\Manager();

        $handler1 = new DummyHandler1(true);
        $handler2 = new DummyHandler1(false);

        $manager->appendHandlerToChain($handler1);
        $manager->appendHandlerToChain($handler2);

        $manager->removeHandlerFromChain($handler1);

        $command = new \NETWAYS\Chain\Command('fibunacci');
        $command['a'] = 1;
        $command['b'] = 1;

        $manager->processRequest($command);

        $this->assertEquals(1, $command['a']);
        $this->assertEquals(2, $command['b']);
    }

    public function testChain4()
    {
        $manager = new \NETWAYS\Chain\Manager();

        $manager->appendHandlerToChain(new DummyReflectionHandler1());
        $manager->appendHandlerToChain(new DummyReflectionHandler1());
        $manager->appendHandlerToChain(new DummyHandler1()); // Mixed mode
        $manager->appendHandlerToChain(new DummyReflectionHandler1());

        $command = new \NETWAYS\Chain\Command('fibunacci');
        $command['a'] = 1;
        $command['b'] = 1;

        $manager->processRequest($command);

        $this->assertEquals(5, $command['a']);
        $this->assertEquals(8, $command['b']);
    }
}
