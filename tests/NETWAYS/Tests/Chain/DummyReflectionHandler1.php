<?php

namespace NETWAYS\Tests\Chain;

class DummyReflectionHandler1 extends \NETWAYS\Chain\ReflectionHandler
{

    public function commandFibunacci(&$a, &$b)
    {
        $c = $a + $b;
        $a = $b;
        $b = $c;
    }

}
