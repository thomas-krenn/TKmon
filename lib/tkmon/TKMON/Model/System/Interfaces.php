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

    const TEMP_PREFIX = 'interfaces-model-';

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
     * Tester if the file was loaded
     * @var bool
     */
    private $loaded = false;

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
     * Clear all options
     */
    public function purgeOptions()
    {
        unset($this->options);
        $this->options = array();
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
    public function hasFlag($flag)
    {
        return array_key_exists($flag, $this->flags);
    }

    /**
     * Set a new flag
     * @param string $flag
     */
    public function setFlag($flag)
    {
        $this->flags[$flag] = true;
    }

    /**
     * Drop the flag
     * @param string $flag
     */
    public function dropFlag($flag)
    {
        if ($this->hasFlag($flag)) {
            unset($this->flags[$flag]);
        }
    }

    /**
     * Clear all flags
     */
    public function purgeFlags()
    {
        unset($this->flags);
        $this->flags = array();
    }


    /**
     * Parser for interface formats
     */
    public function load()
    {
        //$test = file_get_contents($this->getInterfaceFile());
        //var_dump($test);
        //die;

        $file = new \SplFileObject($this->getInterfaceFile(), 'r');
        $match = array();
        $state = false;
        $matchLine = $this->getInterfaceMatchLine();

        foreach ($file as $line) {
            if ($state === false && preg_match($matchLine, $line, $match)) {
                $flags = explode(' ', trim($match[1]));
                foreach ($flags as $flag) {
                    $this->setFlag($flag);
                }
                $state = true;
            } elseif ($state === true && preg_match('@^\s+([^\s]+)\s+([^$]+)$@', $line, $match)) {
                $this->offsetSet($match[1], trim($match[2]));
            } elseif ($state === true && preg_match('@^\s*(#|$)@', $line)) {
                continue;
            } elseif ($state === true) {
                $state = -1;
            }

            if ($state === -1) {
                break;
            }
        }

        $this->loaded = true;
    }

    /**
     * Ready quoted match line for file content
     * @return string
     * @throws \TKMON\Exception\ModelException
     */
    private function getInterfaceMatchLine()
    {
        if (!$this->getInterfaceName()) {
            throw new \TKMON\Exception\ModelException('$interfaceName is missing');
        }

        return '@^iface\s+'. preg_quote($this->getInterfaceName(), '@'). '\s+inet\s+([^$]+)$@';
    }

    /**
     * Detect our cutting window
     *
     * To insert new interface configuration
     *
     * @param int $start start index where to insert
     * @param int $length how many lines belongs to us
     * @param array $lines The file content
     */
    private function detectDataIndex(&$start, &$length, array $lines)
    {
        $set = false;
        foreach ($lines as $i => $line) {
            if ($set && (preg_match('/^iface\s+\w+/', $line) || count($lines)-1 == $i)) {
                $length = $i-$start;

                /*
                 * Go up to find comments
                 */
                for (; $i>0; $i--) {
                    $line = $lines[$i];
                    if (preg_match('/^(#|iface|auto|\s+$)/', $line)===0) {
                        break 2;
                    }
                    $length--;
                }
            }


            if (!$set && preg_match($this->getInterfaceMatchLine(), $line)) {
                $start = $i;
                $set = true;
            }
        }
    }

    /**
     * Generate data we can insert into interfaces file
     * @return array
     */
    private function generateDataToWrite()
    {
        $out = array();
        $out[] = 'iface '
            . $this->getInterfaceName()
            . ' inet '
            . implode(' ', array_keys($this->flags));

        foreach ($this->options as $name => $value) {
            $out[] = "\t". $name. ' '. $value;
        }

        return $out;
    }

    /**
     * Write the data to disk
     * @throws \TKMON\Exception\ModelException
     */
    public function write()
    {
        $lines = array();

        if (is_file($this->getInterfaceFile())) {
            if ($this->loaded === false) {
                throw new \TKMON\Exception\ModelException(
                    "File is present but not loaded,"
                    . " you'll loose all your data, abort!"
                );
            }

            $lines = explode(PHP_EOL, file_get_contents($this->getInterfaceFile()));
        }

        /** @var int $index Index counter where we found the interface **/
        $index = 0;

        /** @var int $length Length items belonging to the interface **/
        $length = 0;

        // Generate index to splice
        $this->detectDataIndex($index, $length, $lines);


        // Write our new config
        array_splice($lines, $index, $length, $this->generateDataToWrite());

        $interfacesFile = new \NETWAYS\IO\RealTempFileObject(self::TEMP_PREFIX, 'w');
        $interfacesFile->fwrite(implode(PHP_EOL, $lines));
        $interfacesFile->fflush();
        $interfacesFile->chmod(0644);

        /** @var $mv \NETWAYS\IO\Process **/
        $mv = $this->container['command']->create('mv');
        $mv->addPositionalArgument($interfacesFile->getRealPath());
        $mv->addPositionalArgument($this->getInterfaceFile());
        $mv->execute();
    }

    /**
     * Bring the object back into startup state
     *
     * So we can write a new interface definition
     */
    public function resetData()
    {
        $this->purgeOptions();
        $this->purgeFlags();
        $this->loaded = false;
        unset($this->interfaceName);
    }
}
