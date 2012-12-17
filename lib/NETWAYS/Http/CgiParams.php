<?php

namespace NETWAYS\Http;

/**
 * CGI Parameter container, contains all
 * relevant parameters.
 *
 * In fact it's a object of ArrayObjects
 *
 * @package NETWAYS\Http
 */
class CgiParams {

    /**
     * What we can hold
     * @var array
     */
    private $namespaces = array(
        'request', 'cookie', 'header'
    );

    /**
     * All the db
     * @var array[ArrayObject}
     */
    private $data = array();

    /**
     * Default namespace
     * @var string
     */
    private $defaultNamespace = 'request';

    /**
     * Creates a new parameter holder object
     */
    public function __construct() {
        $this->initializeData();
    }

    /**
     * Fills up the object with db
     */
    private function initializeData() {
        foreach ($this->namespaces as $namespace) {
            $this->data[$namespace] = new \NETWAYS\Common\ArrayObject();

            $method = 'get'. ucfirst($namespace). 'Data';
            $this->data[$namespace]->setAll(call_user_func(array($this, $method)));
        }
    }

    /**
     * How to get request db
     * @return array
     */
    private function getRequestData()
    {
        return array_merge($_POST, $_GET);
    }

    /**
     * How to get cookie db
     * @return array
     */
    private function getCookieData()
    {
        return $_COOKIE;
    }

    /**
     * How to get header db
     * @return array
     */
    private function getHeaderData()
    {
        return $_SERVER;
    }

    /**
     * @param $ns
     * @return \NETWAYS\Common\ArrayObject
     */
    private function getArrayObject($ns) {
        if ($ns === null) {
            return $this->data[$this->defaultNamespace];
        } elseif (in_array($ns, $this->namespaces) === true) {
            return $this->data[$ns];
        }
    }

    /**
     * Shortcut method to get a param
     * @param $name
     * @param mixed $default
     * @param string $ns
     * @return mixed
     */
    public function getParameter($name, $default=null, $ns=null) {
        return $this->getArrayObject($ns)->get($name, $default);
    }

    /**
     * Shortcut method to set a parameter
     * @param string $name
     * @param mixed $value
     * @param string $ns
     */
    public function setParameter($name, $value, $ns=null) {
        return $this->getArrayObject($ns)->set($name, $value);
    }

    /**
     * Shortcut method to check if we have a parameter
     * @param string $name
     * @param string $ns
     * @return bool
     */
    public function hasParameter($name, $ns=null) {
        return $this->getArrayObject($ns)->offsetExists($name);
    }

    /**
     * Return a array copy
     * @param string $ns
     * @return array
     */
    public function getAll($ns=null) {
        return $this->getArrayObject($ns)->getAll();
    }
}