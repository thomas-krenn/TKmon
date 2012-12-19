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
 * Small class to handle configurations in json files
 *
 * @package NETWAYS\Common
 * @author Marius Hein <marius.hein@netways.de>
 */
class Config extends \ArrayObject
{
    /**
     * Array reference to all the db
     * @var array
     */
    private $data = array();

    /**
     * Constructor, creates a new instance of the object
     */
    public function __construct()
    {
        parent::__construct($this->data);
    }

    /**
     * Loads a directory of files
     * @param string $dir Directory
     */
    public function loadDirectory($dir) {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::CHILD_FIRST);

        // We need sorting cause of config dependencies
        $sorted = new SortedFileIterator($iterator);

        foreach ($sorted as $file) {
            if ($file->isFile()) {
                $this->loadFile($file->getRealPath());
            }
        }
    }

    /**
     * Load a single file
     * @param string $file File
     */
    public function loadFile($file)
    {
        if (is_file($file)) {
            $array = (array)json_decode(file_get_contents($file), true);
            if (count($array)) {
                foreach ($array as $index => $newval) {
                    $this->offsetSet($index, $newval);
                }
            }
        }
    }

    /**
     * Setter method for any value on the configuration store
     * @param string $index Index of configuration item
     * @param mixed $newval Value, anything to store
     */
    public function set($index, $newval)
    {
        return $this->offsetSet($index, $newval);
    }

    /**
     * Method of ArrayObject
     * @param string $index
     * @param mixed $newval
     */
    public function offsetSet($index, $newval)
    {
        if (is_string($newval)) {
            $newval = $this->replaceValueTokens($newval);
        }

        return parent::offsetSet($index, $newval);
    }

    /**
     * Short version of offsetGet. Also can return a
     * default value if item is not found
     *
     * @param string $index Name of the configuration item
     * @param mixed $default If value is not found
     * @return mixed|null The value found of default value
     */
    public function get($index, $default=null)
    {

        if (!$this->offsetExists($index)) {
            return $default;
        }

        $val = $this->offsetGet($index);
        if (!isset($val)) {
            return $default;
        }

        return $val;
    }

    /**
     * Replaces values in the string with content of configuration
     *
     * @param string $val
     * @return string
     */
    private function replaceValueTokens($val)
    {
        $matches = array();

        if (preg_match_all('/(\{([^\}]+)})/', $val, $matches, PREG_SET_ORDER)) {
            for ($i=0; $i<count($matches); $i++) {
                $newval = $this->get($matches[$i][2], 'NOT_FOUND('. $matches[$i][2]. ')');
                $val = preg_replace('/'. preg_quote($matches[$i][1]). '/', $newval, $val);
            }
        }
        return $val;
    }
}