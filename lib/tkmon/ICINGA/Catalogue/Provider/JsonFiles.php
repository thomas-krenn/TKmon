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

use NETWAYS\Intl\SimpleTranslator;

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
     * Cache manager
     * @var \NETWAYS\Cache\Manager
     */
    private $cacheManager;

    /**
     * Cache identifier
     * @var string
     */
    private $cacheIdentifier;

    /**
     * @var SimpleTranslator
     */
    private $translator = null;

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
     * Add directory of files to object
     *
     * The directory is recursively parsed files *.json files
     *
     * @param string $dir directory
     */
    public function addDir($dir)
    {
        $directory = new \RecursiveDirectoryIterator($dir);
        $iterator = new \RecursiveIteratorIterator($directory);
        $regex = new \RegexIterator($iterator, '/^.+\.json/i', \RecursiveRegexIterator::MATCH);

        /** @var $file \SplFileInfo **/
        foreach ($regex as $file) {
            $this->addFile($file->getRealPath());
        }
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

        if ($this->isCached()) {
            $this->loadFromCache();
        } else {
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

            foreach ($this->data as $index => &$entry) {
                $this->languageProcessing($entry);
                $this->index[$entry->_catalogue_attributes->name] = $index;
            }

            $this->writeToCache();
        }
    }

    /**
     * Tests an object for translation information
     * @param \stdClass $object
     * @param $name
     * @return bool
     */
    private function testLanguageObject(\stdClass $object, $name)
    {
        $check = false;
        foreach ($object as $name => $value) {
            if (preg_match('/^\w{2}_\w{2,}$/', $name) && (is_string($value) || is_array($value))) {
                $check = true;
                break;
            }
        }
        return $check;
    }

    /**
     * Recursive language processing
     * @param $object
     */
    private function languageProcessing($object)
    {
        foreach ($object as $attribute => $value) {

            if ($value instanceof \stdClass && $this->testLanguageObject($value, $attribute)) {
                $object->{$attribute} = $this->getTranslator()->translate($value);
            }

            if (is_array($value) || $value instanceof \stdClass) {
                $this->languageProcessing($value);
            }
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

    /**
     * Setter for cache interface
     *
     * @param \NETWAYS\Cache\Manager $cache
     * @param $identifier
     */
    public function setCacheInterface(\NETWAYS\Cache\Manager $cache, $identifier)
    {
        $this->cacheManager = $cache;
        $this->cacheIdentifier = $identifier;
    }

    /**
     * Getter for cache interface
     *
     * @return \NETWAYS\Cache\Manager
     */
    public function getCacheManager()
    {
        return $this->cacheManager;
    }

    /**
     * Getter for cache identifier
     *
     * @return string
     */
    public function getCacheIdentifier()
    {
        return $this->cacheIdentifier;
    }

    /**
     * Test if our data is cached
     * @return bool
     */
    public function isCached()
    {
        if ($this->getCacheManager()) {
            $identifier = $this->getCacheIdentifier();
            $check = $this->getCacheManager()->hasItem($identifier);

            /*
             * Test if the used locale is cached, else drop the cache
             * and let main create the new data
             */
            if ($this->getTranslator()) {
                if ($this->getCacheManager()->hasItem($identifier. '.locale')) {
                    $locale = $this->getCacheManager()->retrieveItem($identifier. '.locale');
                    $check = $check && ($this->getTranslator()->getLocale() == $locale);
                } else {
                    $check = false;
                }
            }

            return $check;
        }

        return false;
    }

    /**
     * Load data from cache into the object
     */
    public function loadFromCache()
    {
        if ($this->getCacheManager()) {
            $data = $this->getCacheManager()->retrieveItem($this->getCacheIdentifier());
            $this->data = (array)$data->data;
            $this->index = (array)$data->index;
        }
    }

    /**
     * Write data from object to cache
     */
    public function writeToCache()
    {
        if ($this->getCacheManager()) {
            $identifier = $this->getCacheIdentifier();
            $data = new \stdClass();
            $data->data = $this->data;
            $data->index = $this->index;
            $this->getCacheManager()->storeItem($data, $identifier);

            if ($this->getTranslator()) {
                $this->getCacheManager()->storeItem($this->getTranslator(), $identifier. '.locale');
            }
        }
    }

    /**
     * Setter for simple translator
     * @param \NETWAYS\Intl\SimpleTranslator $translator
     */
    public function setTranslator(SimpleTranslator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Getter for simple translator
     * @return \NETWAYS\Intl\SimpleTranslator
     */
    public function getTranslator()
    {
        return $this->translator;
    }
}
