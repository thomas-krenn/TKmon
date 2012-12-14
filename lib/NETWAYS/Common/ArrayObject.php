<?php

namespace NETWAYS\Common;

/**
 * Shorter implementation of ArrayObject
 */
class ArrayObject extends \ArrayObject
{
    public function __construct(array $data=null)
    {
        if ($data !== null) {
            parent::__construct($data);
        } else {
            parent::__construct();
        }
    }

    /**
     * Set all data
     * @param array $data
     */
    public function setAll(array $data)
    {
        parent::__construct($data);
    }

    /**
     * Clear all the data
     */
    public function clear() {
        parent::__construct(array());
    }

    /**
     * Getter with default switch
     * @param $index
     * @param mixed $default
     * @return mixed
     */
    public function get($index, $default=null) {
        if ($this->offsetExists($index)) {
            return $this->offsetGet($index);
        }

        return $default;
    }

    /**
     * Setter in short form
     * @param mixed $index
     * @param mixed $newval
     */
    public function set($index, $newval) {
        return $this->offsetSet($index, $newval);
    }

    /**
     * Get all in short form
     * @return array
     */
    public function getAll() {
        return (array)$this->getArrayCopy();
    }
}
