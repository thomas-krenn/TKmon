<?php

namespace NETWAYS\Common;

/**
 * NETWAYS Spl autoloader class which follows the
 * PSR-0 standard to load classes
 */
class ClassLoader
{
    /**
     * @var string
     */
    private $fileExtension = '.php';

    /**
     * @var string
     */
    private $namespaceSeparator = '\\';

    /**
     * @var string
     */
    private $namespace = '';

    /**
     * @var string
     */
    private $includePath = '';

    /**
     * Creates new autoloader instance
     *
     * @param string $namespace
     * @param string $includePath
     */
    public function __construct($namespace, $includePath='')
    {
        $this->setNamespace($namespace);
        $this->setIncludePath($includePath);
    }

    /**
     * @param string $fileExtension
     */
    public function setFileExtension($fileExtension)
    {
        $this->fileExtension = $fileExtension;
    }

    /**
     * @return string
     */
    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    /**
     * @param string $includePath
     */
    public function setIncludePath($includePath)
    {
        $this->includePath = $includePath;
    }

    /**
     * @return string
     */
    public function getIncludePath()
    {
        return $this->includePath;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param string $namespaceSeparator
     */
    public function setNamespaceSeparator($namespaceSeparator)
    {
        $this->namespaceSeparator = $namespaceSeparator;
    }

    /**
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
    public function unregister() {
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

        require_once (($this->includePath !== null) ? $this->includePath. DIRECTORY_SEPARATOR : '')
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
