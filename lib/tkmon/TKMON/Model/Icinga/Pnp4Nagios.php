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

use ICINGA\Base\Object;
use TKMON\Exception\ModelException;
use TKMON\Model\ApplicationModel;

class Pnp4Nagios extends ApplicationModel
{
    const GRAPH_URL = '/graph';

    const IMAGE_URL = '/image';

    private $proxy = false;

    /**
     * @var string
     */
    private $perfdataPath;

    /**
     * @var string
     */
    private $accessUrl;

    /**
     * @var Object
     */
    private $object;

    /**
     * @return string
     */
    public function getPerfdataPath()
    {
        return $this->perfdataPath;
    }

    /**
     * @param string $perfdataPath
     */
    public function setPerfdataPath($perfdataPath)
    {
        $this->perfdataPath = $perfdataPath;
    }

    /**
     * @return string
     */
    public function getAccessUrl($proxyUrl = false)
    {
        if ($this->proxy === true && $proxyUrl === true) {
            return $this->container['config']->get('web.path') . 'Pnp4Nagios';
        }
        return $this->accessUrl;
    }

    /**
     * @param string $accessUrl
     */
    public function setAccessUrl($accessUrl)
    {
        $this->accessUrl = $accessUrl;
    }

    /**
     * @return Object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param \stdClass $object
     * @return $this
     */
    public function setObject(\stdClass $object)
    {
        $this->object = $object;
        return $this;
    }

    public function useProxy($flag = true)
    {
        $this->proxy = (bool)$flag;
    }

    public function assertObject()
    {
        if ($this->object === null) {
            throw new ModelException('Object not set');
        }
    }

    /**
     * Test for host or service chart existence
     *
     * @return bool
     */
    public function hasChart()
    {
        $o = $this->getObject();
        $path = $this->getPerfdataPath() . '/' . $o->host_name;
        if (isset($o->service_description)) {
            $path .= '/' . $o->service_description . '.rrd';
        }
        if (file_exists($path)) {
            return true;
        }
        return false;
    }

    public function getUrl($image = false, $args = array())
    {
        $o      = $this->getObject();
        $path   = $this->getAccessUrl($image) .
            (($image===true) ? self::IMAGE_URL : self::GRAPH_URL);

        $params = array(
            'host'  => $o->host_name
        );
        if (isset($o->service_description)) {
            $params['srv'] = $o->service_description;
        }
        if ($image === true) {
            $params['view'] = 1;
            $params['source'] = 0;
        }
        if (count($args)) {
            $params = array_merge($params, $args);
        }
        return $path . '?' . http_build_query($params);
    }
}
