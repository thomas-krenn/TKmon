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

/**
 * Model to get monitoring information
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class StatusData extends \TKMON\Model\ApplicationModel
{

    /**
     * PHP proxy to fetch data from icinga
     * @var \NETWAYS\Http\SimpleProxy
     */
    private $proxy;

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
    }

    /**
     * Return data structure
     *
     * Fetch data from proxy and convert json to a object structure
     *
     * @return \stdClass
     */
    private function createObjectData()
    {
        $data = $this->proxy->getContent();
        $this->proxy->purgeOptions();
        return json_decode($data, false);
    }

    /**
     * Fetch current service status

     * @param null|int $serviceStatusTypes
     * @return \stdClass
     */
    public function getServiceStatus($serviceStatusTypes = null)
    {

        $requestUri = '/cgi-bin/icinga/status.cgi';

        if ($serviceStatusTypes !== null) {
            $this->proxy->addParam('servicestatustypes', (int)$serviceStatusTypes);
        }

        $this->proxy->setRequestUrl($requestUri);
        return $this->createObjectData();
    }

    /**
     * Return a eventlog stream
     * @return \stdClass
     */
    public function getEventLog()
    {
        $this->proxy->setRequestUrl('/cgi-bin/icinga/showlog.cgi');
        $this->proxy->addParam('limit', 200);
        return $this->createObjectData();
    }
}
