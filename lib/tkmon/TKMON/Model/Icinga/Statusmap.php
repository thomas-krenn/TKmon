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
 * @copyright 2012-2015 NETWAYS GmbH <info@netways.de>
 */

namespace TKMON\Model\Icinga;

use NETWAYS\Http\Exception\SimpleProxyException;
use TKMON\Model\ApplicationModel;

/**
 * Fetch statusmap data via simple proxy
 */
class Statusmap extends ApplicationModel
{
    /**
     * PHP proxy to fetch data from icinga
     * @var \NETWAYS\Http\SimpleProxy
     */
    private $proxy;

    /**
     * Binary image data
     * 
     * @var string
     */
    private $image;

    /**
     * Default parameters to use
     * 
     * @var array
     */
    private static $defaultParams = array(
        'host'          => 'all',
        'createimage'   => '1',
        'canvas_x'      => 0,
        'canvas_y'      => 0,
        'canvas_width'  => 9999,
        'canvas_height' => 9999,
        'max_width'     => 0,
        'max_height'    => 0,
        'layout'        => 5,
        'layermode'     => 'exclude'
    );

    /**
     * User provided parameters
     * 
     * @var array
     */
    private $params = array();

    /**
     * Create a object
     * @param \Pimple $container
     */
    public function __construct(\Pimple $container)
    {
        parent::__construct($container);

        /** @var $config \NETWAYS\Common\Config */
        $config = $container['config'];

        $this->proxy = new \NETWAYS\Http\SimpleProxy();

        $this->proxy->setBaseUrl($config->get('icinga.baseurl'));
        $this->proxy->addParam('jsonoutput', 'yes');

        $this->proxy->setHttpAuth(
            $config->get('icinga.tkuser'),
            $config->get('icinga.tkpasswd')
        );

        $this->proxy->setRequestUrl('/cgi-bin/icinga/statusmap.cgi');
    }

    /**
     * Create image data
     * 
     * @return string
     */
    private function createImageData()
    {
        foreach (self::$defaultParams as $key => $value) {
            $this->proxy->addParam($key, $value);
        }

        foreach ($this->params as $key => $param) {
            if (strlen($param)) {
                $this->proxy->addParam($key, $param);
            }
        }

        $this->proxy->addParam('time', time());

        return $this->proxy->getContent();
    }

    /**
     * Get image data
     * 
     * @return string
     */
    public function getImage()
    {
        return $this->createImageData();
    }

    /**
     * Get proxy request info
     * 
     * @return array|\NETWAYS\Http\misc
     * 
     * @throws SimpleProxyException
     */
    public function getInfo()
    {
        return $this->proxy->getInfo();
    }

    /**
     * Get keys of default parameter 
     * 
     * @return array
     */
    public function getDefaultParamKeys()
    {
        return array_keys(self::$defaultParams);
    }

    /**
     * Add user parameter
     * 
     * @param string $key
     * @param mixed $value
     */
    public function addParam($key, $value)
    {
        $this->params[$key] = $value;
    }
}