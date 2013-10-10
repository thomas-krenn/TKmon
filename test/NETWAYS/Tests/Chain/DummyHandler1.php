<?php

namespace NETWAYS\Tests\Chain;

use NETWAYS\Chain\Interfaces\HandlerInterface;

class DummyHandler1 implements HandlerInterface
{

    private $doThrow=false;

    public function __construct($doThrow = false) {
        $this->doThrow = $doThrow;
    }

    public function processRequest(\NETWAYS\Chain\Interfaces\CommandInterface $command)
    {
        if ($command->getCommandName() === 'fibunacci') {
            $c = $command['a'] + $command['b'];
            $command['a'] = $command['b'];
            $command['b'] = $c;
        }
        
        if ($this->doThrow === true) {
            throw new \NETWAYS\Chain\Exception\HandlerException('BOOM');
        }
    }

}
