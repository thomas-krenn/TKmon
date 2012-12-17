<?php

namespace TKMON\Mvc\Output;

class Json extends \NETWAYS\Common\ArrayObject implements DataInterface
{

    public function getData()
    {
        return $this->getArrayCopy();
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString()
    {
        return json_encode((array)$this);
    }

}
