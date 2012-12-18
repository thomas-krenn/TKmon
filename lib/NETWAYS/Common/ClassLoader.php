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
 * NETWAYS Spl autoloader class which follows the
 * PSR-0 standard to load classes
 *
 * @package NETWAYS\Common
 * @author Marius Hein <marius.hein@netways.de>
 */
class ClassLoader
{
    /**
     * File extensions
     * @var string
     */
    private $fileExtension = '.php';

    /**
     * Namespace separator
     * @var string
     */
    private $namespaceSeparator = '\\';

    /**
     * Name space responsible for
     * @var string
     */
    private $namespace = '';

    /**
     * Path where including starts
     * @var string
     */
    private $includePath = '';

    /**
     * Creates new autoloader instance
     *
     * @param string $namespace
     * @param string $includePath
     */
    public function __construct($namespace, $includePath = '')
    {
        $this->setNamespace($namespace);
        $this->setIncludePath($includePath);
    }

    /**
     * Setter for fileExtension
     *
     * @param string $fileExtension
     */
    public function setFileExtension($fileExtension)
    {
        $this->fileExtension = $fileExtension;
    }

    /**
     * Getter for fileExtension
     *
     * @return string
     */
    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    /**
     * Setter for includePath
     *
     * @param string $includePath
     */
    public function setIncludePath($includePath)
    {
        $this->includePath = $includePath;
    }

    /**
     * Getter for includePath
     *
     * @return string
     */
    public function getIncludePath()
    {
        return $this->includePath;
    }

    /**
     * Setter for namespace
     *
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * Getter for namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Setter for namespaceSeparator
     *
     * @param string $namespaceSeparator
     */
    public function setNamespaceSeparator($namespaceSeparator)
    {
        $this->namespaceSeparator = $namespaceSeparator;
    }

    /**
     * Getter for namespaceSeparator
     *
     * @return string
     */
    public function getNamespaceSeparator()
    {
        return $this->namespaceSeparator;
    }

    /**
     * Register a SPL autoloader method
     */
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    /**
     * Unregister SPL autoloader method
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    /**
     * Loads a class by name
     * @param string $className
     * @return boolean
     */
    public function loadClass($className)
    {
        if (!$this->checkNamespace($className)) {
            return false;
        }

        require_once (($this->includePath !== null) ? $this->includePath . DIRECTORY_SEPARATOR : '')
            . $this->getClassFile($className);

        return true;
    }

    /**
     * Decide if we have the right class name
     *
     * @param string $className
     * @return bool
     */
    private function checkNamespace($className)
    {
        if ($this->namespace && strpos($className, $this->namespace) === 0) {
            return true;
        }

        return false;
    }

    /**
     * Returns the file name extracted from class name
     *
     * @param $className
     * @return mixed
     */
    private function getClassFile($className)
    {
        return str_replace('_', DIRECTORY_SEPARATOR,
            str_replace($this->namespaceSeparator, DIRECTORY_SEPARATOR, $className))
            . $this->fileExtension;
    }
}
