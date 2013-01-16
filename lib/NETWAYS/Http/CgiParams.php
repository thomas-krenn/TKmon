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

namespace NETWAYS\Http;

/**
 * CGI Parameter container, contains all
 * relevant parameters.
 *
 * In fact it's a object of ArrayObjects
 *
 * @package NETWAYS\Http
 * @author Marius Hein <marius.hein@netways.de>
 */
class CgiParams
{
    /**
     * What we can hold
     * @var array
     */
    private $namespaces = array(
        'header', 'request', 'cookie'
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
    public function __construct()
    {
        $this->initializeData();
    }

    /**
     * Fills up the object with data
     */
    private function initializeData()
    {
        foreach ($this->namespaces as $namespace) {
            $this->data[$namespace] = new \NETWAYS\Common\ArrayObject();

            $method = 'get' . ucfirst($namespace) . 'Data';
            $this->data[$namespace]->setAll(call_user_func(array($this, $method)));
        }
    }

    /**
     * Json detector
     *
     * Tries to detect json in bad configured http requests
     *
     * @return string|null
     */
    private function getJsonBody()
    {
        if ($this->getParameter('CONTENT_LENGTH', 0, 'header') > 0) {
            $json = file_get_contents('php://input');
            if ($json) {
                return json_decode($json, true);
            }
        }
    }

    /**
     * How to get request data
     *
     * This method determines json data and converts HTTP RAW DATA into
     * a associative data array we can use in CGI params
     *
     * @return array
     */
    private function getRequestData()
    {
        $data = $this->getJsonBody();

        if ($data === null) {
            $data = array_merge($_POST, $_GET);
        }

        return filter_var_array(
            $data,
            FILTER_SANITIZE_STRING
        );
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
     * Returns the underlaying object by namespace
     *
     * @param $ns
     * @return \NETWAYS\Common\ArrayObject
     */
    public function getArrayObject($ns)
    {
        if ($ns === null) {
            return $this->data[$this->defaultNamespace];
        } elseif (in_array($ns, $this->namespaces) === true) {
            return $this->data[$ns];
        }
        return null;
    }

    /**
     * Shortcut method to get a param
     * @param $name
     * @param mixed $default
     * @param string $ns
     * @return mixed
     */
    public function getParameter($name, $default = null, $ns = null)
    {
        return $this->getArrayObject($ns)->get($name, $default);
    }

    /**
     * Shortcut method to set a parameter
     * @param string $name
     * @param mixed $value
     * @param string $ns
     */
    public function setParameter($name, $value, $ns = null)
    {
        return $this->getArrayObject($ns)->set($name, $value);
    }

    /**
     * Shortcut method to check if we have a parameter
     * @param string $name
     * @param string $ns
     * @return bool
     */
    public function hasParameter($name, $ns = null)
    {
        return $this->getArrayObject($ns)->offsetExists($name);
    }

    /**
     * Return a array copy
     * @param string $ns
     * @return array
     */
    public function getAll($ns = null)
    {
        $obj = $this->getArrayObject($ns);
        if ($obj instanceof \NETWAYS\Common\ArrayObject) {
            return $obj->getAll();
        }

        return $obj;
    }
}
