<?php

namespace TKMON\Mvc\Output;

/**
 *
 */
class SimpleString implements DataInterface
{
    /**
     * @var string
     */
    protected $data;

    /**
     * @param string $string
     */
    public function __construct($string) {
        $this->data = $string;
    }

    public function __toString() {
        return $this->data;
    }

    public function toString() {
        return $this->data;
    }

    public function getData() {
        return $this->data;
    }
}
