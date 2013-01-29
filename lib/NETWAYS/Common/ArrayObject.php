<?php
/**
 * This file is part of TKMON
 *
 * TKMON is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TKMON is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TKMON.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Marius Hein <marius.hein@netways.de>
 * @copyright 2012-2013 NETWAYS GmbH <info@netways.de>
 */

namespace NETWAYS\Common;

/**
 * Extended implementation ArrayObject
 *
 * @package NETWAYS\Common
 * @author Marius Hein <marius.hein@netways.de>
 */
class ArrayObject extends \ArrayObject
{
    /**
     * Creates a new object of this type
     * @param array $data
     */
    public function __construct(array $data = null)
    {
        if ($data !== null) {
            parent::__construct($data);
        } else {
            parent::__construct();
        }
    }

    /**
     * Set all db
     * @param array $data
     */
    public function setAll(array $data)
    {
        parent::__construct($data);
    }

    /**
     * Clear all the db
     */
    public function clear()
    {
        parent::__construct(array());
    }

    /**
     * Getter with default switch
     * @param $index
     * @param mixed $default
     * @return mixed
     */
    public function get($index, $default = null)
    {
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
    public function set($index, $newval)
    {
        return $this->offsetSet($index, $newval);
    }

    /**
     * Get all in short form
     * @return array
     */
    public function getAll()
    {
        return (array)$this->getArrayCopy();
    }

    /**
     * Copy properties
     *
     * @param \ArrayObject $object
     */
    public function fromArrayObject(\ArrayObject $object)
    {
        foreach ($object as $key => $val) {
            $this->offsetSet($key, $val);
        }
    }

    /**
     * Merge stdClass properties into object
     * @param \stdClass $object
     */
    public function mergeStdClass(\stdClass $object)
    {
        foreach ($object as $key => $val) {
            $this[$key] = $val;
        }
    }
}
