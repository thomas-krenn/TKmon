<?php

namespace NETWAYS\Tests\Chain;

class DummyReflectionHandler2 extends \NETWAYS\Chain\ReflectionHandler
{

    public function commandFibunacci(DataStruct $ds)
    {
        $c = $ds->a + $ds->b;
        $ds->a = $ds->b;
        $ds->b = $c;
    }

}
