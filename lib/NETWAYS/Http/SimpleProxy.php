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
 * Proxy class to fetch embedded data from a website
 *
 * @package NETWAYS\Http
 * @author Marius Hein <marius.hein@netways.de>
 */
class SimpleProxy
{
    /**
     * Base url
     * @var string
     */
    private $baseUrl;

    /**
     * Url, concatenated to base url
     * @var
     */
    private $requestUrl;

    /**
     * cUrl resource
     * @var resource
     */
    private $curlHandler;

    /**
     * Array of cUrl options
     * @var array
     */
    private $curlOptions = array();

    /**
     * HTTP header array
     * @var array
     */
    private $httpHeader = array();

    /**
     * HTTP simple auth data
     * @var array
     */
    private $httpAuth = array();

    /**
     * CGI params
     * @var array
     */
    private $params = array();

    /**
     * Content to return
     * @var string
     */
    private $content;

    /**
     * Info after request
     * @var array
     */
    private $info = array();

    /**
     * Create a new object
     * @param string|null $baseUrl
     */
    public function __construct($baseUrl = null)
    {
        $this->curlHandler = $this->initCurlHandler();

        if ($baseUrl !== null) {
            $this->setBaseUrl($baseUrl);
        }
    }

    /**
     * Creates a new cUrl handler
     * @return resource
     * @throws Exception\SimpleProxyException
     */
    private function initCurlHandler()
    {
        if (function_exists('curl_init') === false) {
            throw new \NETWAYS\Http\Exception\SimpleProxyException("Extension cUrl is not loaded");
        }

        return curl_init();
    }

    /**
     * Delete the curl handler
     */
    public function __destruct()
    {
        if (is_resource($this->curlHandler)) {
            curl_close($this->curlHandler);
        }
    }

    /**
     * Setter for the default user agent
     *
     * Sets chrome environment from ubuntu
     */
    public function setDefaultUserAgent()
    {
        $this->setOption(CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.22 (KHTML, like Gecko) ');
    }

    /**
     * Setter for CGI params
     * @param array $params
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * Getter for params
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Add a single param
     * @param $name
     * @param $value
     */
    public function addParam($name, $value)
    {
        $this->params[$name] = $value;
    }

    /**
     * Remove a single param
     * @param $name
     */
    public function removeParam($name)
    {
        if (array_key_exists($name, $this->params)) {
            unset($this->params[$name]);
        }
    }

    /**
     * Remove all params
     */
    public function purgeParams()
    {
        unset($this->params);
        $this->params = array();
    }

    /**
     * Set base URL
     * @param $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Getter for base URL
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Setter request path
     * @param $requestUrl
     */
    public function setRequestUrl($requestUrl)
    {
        $this->requestUrl = $requestUrl;
    }

    /**
     * Getter request path
     * @return mixed
     */
    public function getRequestUrl()
    {
        return $this->requestUrl;
    }

    /**
     * Set basic auth credentials
     * @param $username
     * @param $password
     */
    public function setHttpAuth($username, $password)
    {
        $this->httpAuth = array($username, $password);
    }

    /**
     * Set cURL option
     * @param $option
     * @param $value
     */
    public function setOption($option, $value)
    {
        $this->curlOptions[$option] = $value;
    }

    /**
     * Remote cURL option
     * @param $option
     */
    public function unsetOption($option)
    {
        if (array_key_exists($option, $this->curlOptions)) {
            unset($this->curlOptions[$option]);
        }
    }

    /**
     * Test if option exists
     * @param $option
     * @return bool
     */
    public function hasOption($option)
    {
        return array_key_exists($option, $this->curlOptions);
    }

    /**
     * Remove all options
     */
    public function purgeOptions()
    {
        unset($this->curlOptions);
        $this->curlOptions = array();
    }

    /**
     * Get an option
     * @param string $option
     * @return null|mixed
     */
    public function getOption($option)
    {
        if ($this->hasOption($option)) {
            return $this->curlOptions[$option];
        }

        return null;
    }

    public function getOptions()
    {
        return $this->curlOptions;
    }

    /**
     * Normalize http header names
     * @param $name
     * @return string
     */
    private function normalizeHttpHeader($name)
    {
        return strtolower($name);
    }

    /**
     * Add a http header
     * @param $name
     * @param $value
     */
    public function addHttpHeader($name, $value)
    {
        $this->httpHeader[$this->normalizeHttpHeader($name)] = $value;
    }

    /**
     * Remove a header
     * @param $name
     */
    public function removeHttpHeader($name)
    {
        $name = $this->normalizeHttpHeader($name);
        if (array_key_exists($name, $this->httpHeader)) {
            unset($this->httpHeader[$name]);
        }
    }

    /**
     * Remove all headers
     */
    public function purgeHttpHeader()
    {
        unset($this->httpHeader);
        $this->httpHeader = array();
        return curl_setopt($this->curlHandler, CURLOPT_HTTPHEADER, array());
    }

    /**
     * Build the whole URL
     * @return string
     */
    public function createRequestUrl()
    {
        $request = $this->getBaseUrl(). $this->getRequestUrl();

        if (count($this->params)) {
            $queryString = http_build_query($this->params);
            $request .= '?'. $queryString;
        }

        return $request;
    }

    /**
     * Write cURL options to resource
     */
    private function writeOptions()
    {
        if (count($this->httpHeader)) {
            $headerArray = array();
            foreach ($this->httpHeader as $name => $value) {
                $sanitizedName = ucfirst($name);
                $headerArray[] = $sanitizedName. ': '. $value;
            }
            $this->setOption(CURLOPT_HTTPHEADER, $headerArray);
        }

        $this->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->setOption(CURLOPT_HEADER, false);
        $this->setOption(CURLINFO_HEADER_OUT, true);

        if (count($this->httpAuth) === 2) {
            $this->setOption(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            $this->setOption(CURLOPT_USERPWD, implode(':', $this->httpAuth));
        }

        if (!$this->hasOption(CURLOPT_USERAGENT)) {
            $this->setDefaultUserAgent();
        }

        if (!$this->hasOption(CURLOPT_URL)) {
            $this->setOption(CURLOPT_URL, $this->createRequestUrl());
        }

        curl_setopt_array($this->curlHandler, $this->curlOptions);
    }

    /**
     * Make a request and return the value
     * @return string|boolean
     */
    public function getContent()
    {
        if (!$this->content) {
            $this->doRequest();
        }

        return $this->content;
    }

    /**
     * Fires the request
     */
    public function doRequest()
    {
        $this->writeOptions();
        $this->content = curl_exec($this->curlHandler);
        $this->info = curl_getinfo($this->curlHandler);

        $return = (int)$this->getInfo(CURLINFO_HTTP_CODE);

        // Something went wrong
        // 400 >= Client error
        // 500 >= Server error
        // http://httpstatus.es/

        if ($return >= 500) {
            throw new \NETWAYS\Http\Exception\SimpleProxyException('Server error: '. $return);
        } elseif ($return >= 400) {
            throw new \NETWAYS\Http\Exception\SimpleProxyException('Client error: '. $return);
        }
    }

    /**
     * @param null $type
     * @return array|misc
     * @throws Exception\SimpleProxyException
     */
    public function getInfo($type = null)
    {
        if (!count($this->info)) {
            throw new \NETWAYS\Http\Exception\SimpleProxyException("No request was sent before");
        }

        if ($type) {
            return curl_getinfo($this->curlHandler, $type);
        }

        return $this->info;
    }
}
