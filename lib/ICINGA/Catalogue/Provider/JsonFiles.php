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

namespace ICINGA\Catalogue\Provider;

/**
 * Query for data in json files
 *
 * @package ICINGA
 * @author Marius Hein <marius.hein@netways.de>
 */
class JsonFiles extends \ICINGA\Base\CatalogueProvider
{
    /**
     * Array of json files
     * @var array|string
     */
    private $files = array();

    /**
     * Data store
     * @var array
     */
    private $data = array();

    /**
     * Index of unique object names
     *
     * @var array
     */
    private $index = array();

    /**
     * Set files to load
     *
     * @param array|string $files
     */
    public function setFiles($files)
    {
        $this->files = $files;
    }

    /**
     * Getter for giles
     *
     * @return array|string
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Add a single file to load
     * @param string $file
     */
    public function addFile($file)
    {
        $this->files[] = $file;
    }

    /**
     * Resets data and index
     *
     * To load new values into object
     */
    public function resetData()
    {
        unset($this->data);
        unset($this->index);

        $this->data = array();
        $this->index = array();
    }

    /**
     * Initialize the provider
     *
     * - make ready
     * - throw errors
     *
     * @throws \NETWAYS\Chain\Exception\HandlerException
     * @return void
     */
    public function commandInitialize()
    {
        $this->resetData();

        foreach ($this->getFiles() as $file) {
            if (file_exists($file)) {
                $data = json_decode(file_get_contents($file), false);

                if (isset($data->data) && is_array($data->data)) {
                    $this->data = array_merge($this->data, $data->data);
                }
            } else {
                throw new \NETWAYS\Chain\Exception\HandlerException('Could not load file: '. $file);
            }
        }

        foreach ($this->data as $index => $entry) {
            $this->index[$entry->_catalogue_attributes->name] = $index;
        }
    }

    /**
     * Query for items
     *
     * @param \NETWAYS\Common\ArrayObject $result
     * @param string $query
     * @return void
     */
    public function commandQuery(\NETWAYS\Common\ArrayObject $result, $query)
    {
        $cb = function (\stdClass $item) use ($query) {
            return
                strpos($item->_catalogue_attributes->name, $query) !== false
                || strpos($item->_catalogue_attributes->label, $query) !== false
                || strpos($item->_catalogue_attributes->description, $query) !== false
                || in_array($query, $item->_catalogue_attributes->tags);
        };

        $localFound = array_filter($this->data, $cb);

        foreach ($localFound as $local) {
            $result[] = $local;
        }
    }


    /**
     * Return an item from catalogue
     *
     * @param \stdClass $voyager
     * @param string $name
     * @return \ICINGA\Base\Object|void
     */
    public function commandGetItem(\stdClass $voyager, $name)
    {
        if (array_key_exists($name, $this->index)) {
            $voyager->data = $this->data[$this->index[$name]];
        }
    }
}
