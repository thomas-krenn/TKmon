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

namespace ICINGA\Loader;

/**
 * Filesystem
 *
 * Loads and writes data from and to filesystem
 *
 * @package ICINGA
 * @author Marius Hein <marius.hein@netways.de>
 */
class FileSystem extends \NETWAYS\Common\ArrayObject
{
    /**
     * Filter for the iterator
     */
    const FILE_FILTER = '\.cfg$';

    /**
     * Suffix when create files
     */
    const FILE_SUFFIX = '.cfg';

    /**
     * Path to load from
     * @var string
     */
    private $path;

    /**
     * Flag to drop all data before write
     * @var bool
     */
    private $dropAllBeforeWrite = false;

    /**
     * Strategy to use
     * @var \ICINGA\LoaderStrategyInterface
     */
    private $strategy;

    /**
     * Setter for the path
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Getter for the path
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Flag setter to drop all before load
     * @param bool $dropAll
     */
    public function setDropAllFlag($dropAll = true)
    {
        $this->dropAllBeforeWrite = $dropAll;
    }

    /**
     * Setter for strategy
     * @param \ICINGA\LoaderStrategyInterface $strategy
     */
    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * Getter for strategy
     * @return \ICINGA\LoaderStrategyInterface
     */
    public function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * Drop all files
     *
     * Thia is needed if you use this as database. Drop all first,
     * then write again
     */
    public function dropAll()
    {
        $iterator = new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->getPath(), \RecursiveDirectoryIterator::CURRENT_AS_FILEINFO)
            ),
            '/'. self::FILE_FILTER. '/'
        );

        /** @var $fileInfo \SplFileInfo */
        $fileInfo = null;

        foreach ($iterator as $fileInfo) {
            unlink($fileInfo->getRealPath());
        }
    }

    /**
     * Loads a single file into object
     * @param \SplFileInfo $file
     */
    private function loadFile(\SplFileInfo $file)
    {
        $fo = $file->openFile('r');
        $match = array();

        /** @var $context \ICINGA\Object\Struct */
        $context = null;

        foreach ($fo as $line) {

            // Remove unwanted content
            $line = preg_replace('/[#;].+$/', '', $line);

            if ($context === null && preg_match('/define\s+(\w+)\s*\{/', $line, $match)) {
                $context = new \ICINGA\Object\Struct();
                $context->setObjectType($match[1]);
            } elseif ($context && preg_match('/(\w+)\s+([^$]+)/', $line, $match)) {
                $context[$match[1]] = trim($match[2]);
            } elseif ($context && preg_match('/\}/', $line)) {
                // Trigger interface
                $this->strategy->newObject($context);
                $context = null;
            }
        }

        unset($fo);
    }

    /**
     * Loads the directory
     * @throws \ICINGA\Exception\LoadException
     * @throws \ICINGA\Exception\ConfigException
     */
    public function load()
    {
        if (is_dir($this->getPath()) === false) {
            throw new \ICINGA\Exception\LoadException('Could not load from any other that a directory');
        }

        if (!$this->strategy instanceof \ICINGA\Interfaces\LoaderStrategyInterface) {
            throw new \ICINGA\Exception\ConfigException('Strategy is not configured');
        }

        $iterator = new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->getPath(), \RecursiveDirectoryIterator::CURRENT_AS_FILEINFO)
            ),
            '/'. self::FILE_FILTER. '/'
        );

        /** @var $fileInfo \SplFileInfo */
        $fileInfo = null;

        // Trigger interface
        $this->strategy->beginLoading();

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                $this->loadFile($fileInfo);
            }
        }

        // Trigger interface
        $this->strategy->finishLoading();

        // Trigger interface
        $this->fromArrayObject($this->strategy->getObjects());
    }

    /**
     * Writes tree to file system
     * @throws \ICINGA\Exception\WriteException
     * @throws \ICINGA\Exception\ConfigException
     */
    public function write()
    {

        if (!file_exists($this->getPath())) {
            throw new \ICINGA\Exception\ConfigException('Could not write to path');
        }

        if ($this->dropAllBeforeWrite === true) {
            $this->dropAll();
        }

        /** @var $object \ICINGA\Base\Object */
        $object = null;

        foreach ($this as $object) {
            if ($object instanceof \ICINGA\Base\Object) {
                $fileName = $this->getPath()
                    . DIRECTORY_SEPARATOR
                    . $object->getObjectIdentifier()
                    . self::FILE_SUFFIX;


                $fo = new \SplFileObject($fileName, 'w+');
                $fo->fwrite((string)$object);
                $fo->fflush();
                unset($fo);

            } else {
                throw new \ICINGA\Exception\WriteException('Could not write object: '. get_class($object));
            }
        }
    }
}
