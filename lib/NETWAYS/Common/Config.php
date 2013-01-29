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
     * Persister to store configuration
     * @var \NETWAYS\Common\Config\PersisterInterface
     */
    private $persister;

    /**
     * Flag to control updates
     * @var bool
     */
    private $allowUpdate = true;

    /**
     * Setter for a persister
     * @param \NETWAYS\Common\Config\PersisterInterface $persister
     */
    public function setPersister($persister)
    {
        $this->persister = $persister;
    }

    /**
     * Returns the current persister
     * @return \NETWAYS\Common\Config\PersisterInterface
     */
    public function getPersister()
    {
        return $this->persister;
    }

    /**
     * Allow updating to persister
     */
    public function allowUpdates()
    {
        $this->allowUpdate = true;
    }

    /**
     * Stop updating to persister
     */
    public function stopUpdates()
    {
        $this->allowUpdate = false;
    }

    /**
     * Internal work method to control values in the persister
     * @param string $key
     * @param string $value
     * @param bool $delete
     */
    private function persist($key, $value, $delete = false)
    {
        if ($this->allowUpdate === true && $this->persister !== null) {
            if ($delete === true) {
                $this->persister->drop($key);
            } else {
                $this->persister->persist($key, $value);
            }
        }
    }

    /**
     * Drops all data in the object
     */
    public function clear()
    {
        if ($this->allowUpdate === true && $this->persister !== null) {
            $this->persister->purge();
        }
        parent::__construct(array());
    }

    /**
     * Loads a directory of files
     * @param string $dir Directory
     */
    public function loadDirectory($dir)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

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
            $array = (array)json_decode(file_get_contents($file), false);
            if (count($array)) {
                foreach ($array as $index => $newval) {
                    $this->offsetSet($index, $newval);
                }
            }
        } else {
            throw new \NETWAYS\Common\Exception\ConfigException('File does not exist: '. $file);
        }
    }

    /**
     * Loads the config object from external interfaces
     * @param Config\LoadInterface $loader
     */
    public function load(\NETWAYS\Common\Config\LoadInterface $loader)
    {
        foreach ($loader as $key => $val) {
            $this->set($key, $val);
        }
    }

    /**
     * Setter method for any value on the configuration store
     * @param string $index Index of configuration item
     * @param mixed $newval Value, anything to store
     */
    public function set($index, $newval)
    {
        $this->offsetSet($index, $newval);
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

        $this->persist($index, $newval);

        return parent::offsetSet($index, $newval);
    }

    /**
     * Delete items from object
     * @param mixed $index
     */
    public function offsetUnset($index)
    {
        $this->persist($index, null, true);
        parent::offsetUnset($index);
    }

    /**
     * Short version of offsetGet. Also can return a
     * default value if item is not found
     *
     * @param string $index Name of the configuration item
     * @param mixed $default If value is not found
     * @return mixed|null The value found of default value
     */
    public function get($index, $default = null)
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
            for ($i = 0; $i < count($matches); $i++) {
                $newval = $this->get($matches[$i][2], 'NOT_FOUND(' . $matches[$i][2] . ')');
                $val = preg_replace('/' . preg_quote($matches[$i][1]) . '/', $newval, $val);
            }
        }
        return $val;
    }
}
