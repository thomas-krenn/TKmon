<?php

namespace NETWAYS\Common;

/**
 * Small class to handle configurations in json files
 * @package NETWAYS\Common
 */
class Config extends \ArrayObject
{
    /**
     * Array reference to all the data
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

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $this->loadFile($file->getPathname());
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
        $val = $this->offsetGet($index);
        if (!isset($val)) {
            return $default;
        }

        return $val;
    }

    /**
     * @param string $val
     * @return string
     */
    private function replaceValueTokens($val)
    {
        static $max_iterations = 20;

        $matches = array();
        $i = 0;

        while (preg_match_all('/(\{([^\}]+)})/', $val, $matches, PREG_SET_ORDER)) {
            for ($i=0; $i<count($matches); $i++) {
                $newval = $this->get($matches[$i][2], 'NOT_FOUND('. $matches[$i][2]. ')');
                $val = preg_replace('/'. preg_quote($matches[$i][1]). '/', $newval, $val);
            }

            if ((++$i) > $max_iterations) {
                break;
            }
        }
        return $val;
    }
}