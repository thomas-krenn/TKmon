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

namespace TKMON\Model\System;

/**
 * Writing and configure interfaces
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class Interfaces extends \TKMON\Model\ApplicationModel implements \ArrayAccess, \Countable
{

    /**
     * Interface options
     * e.g. address, netmask, ...
     * @var array
     */
    private $options = array();

    /**
     * Flags for the interface e.g. static or dhcp
     * @var array
     */
    private $flags = array();

    /**
     * Name of the interface
     * e.g. eth0 or eth11
     * @var string
     */
    private $interfaceName;

    /**
     * Interface file (ubuntu specific)
     * @var string
     */
    private $interfaceFile = '/etc/network/interfaces';

    /**
     * Setter for the file
     * @param string $interfaceFile
     */
    public function setInterfaceFile($interfaceFile)
    {
        $this->interfaceFile = $interfaceFile;
    }

    /**
     * Getter for the file
     * @return string
     */
    public function getInterfaceFile()
    {
        return $this->interfaceFile;
    }

    /**
     * Setter for interfaceName
     *
     * mandatory
     *
     * @param string $interfaceName
     */
    public function setInterfaceName($interfaceName)
    {
        $this->interfaceName = $interfaceName;
    }

    /**
     * Getter for interface name
     *
     * @return string
     */
    public function getInterfaceName()
    {
        return $this->interfaceName;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->options);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->options[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->options[$offset] = $value;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->options[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->options);
    }

    /**
     * Testing for flags
     * @param $flag
     * @return bool
     */
    public function hasFlag($flag) {
        return in_array($this->flags, $flag);
    }

    /**
     * Parser for interface formats
     */
    public function load()
    {
        $file = new \SplFileObject($this->getInterfaceFile(), 'r');
        $matchLine = '@^iface\s+'. preg_quote($this->getInterfaceName(), '@'). '\s+inet\s+([^$]+)$@';
        $match = array();
        $state = false;

        while (($line = $file->fgets())) {
            if ($state === false && preg_match($matchLine, $line, $match)) {
                $flags = explode(' ', trim($match[1]));
                $this->flags = $flags;
                $state = true;
            } elseif ($state === true && preg_match('@^\t+[^#]([^\s]+)\s+([^$]+)$@', $line, $match)) {
                $this->offsetSet($match[1], trim($match[2]));
            } elseif ($state === true && preg_match('@^\t*(#|$)@', $line)) {
                continue;
            } elseif ($state === true) {
                $state = -1;
            }

            if ($state === -1) {
                break;
            }
        }
    }

    /**
     * Write the data to disk
     */
    public function write()
    {
        $lines = explode(PHP_EOL, file_get_contents($this->getInterfaceFile()));
        var_dump($lines);
    }
}
