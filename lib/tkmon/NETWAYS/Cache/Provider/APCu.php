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


namespace NETWAYS\Cache\Provider;

use NETWAYS\Cache\Interfaces\Provider as ProviderInterface;

/**
 * Provider to implement APC user cache
 */
class APCu implements ProviderInterface
{
    /**
     * Cache TTL
     *
     * @var int
     */
    private $timeToLive = 0;

    /**
     * @return int
     */
    public function getTimeToLive()
    {
        return $this->timeToLive;
    }

    /**
     * @param int $timeToLive
     */
    public function setTimeToLive($timeToLive)
    {
        $this->timeToLive = $timeToLive;
    }

    public function testConfiguration()
    {
        if (!function_exists('apcu_fetch') || !function_exists('apcu_store') || !extension_loaded('apcu')) {
            throw new \NETWAYS\Cache\Exception\ConfigurationErrorException('APCu extension not available');
        }
    }

    public function storeItem($item, $identifier = null)
    {
        return apcu_store($identifier, json_encode($item), $this->timeToLive);
    }

    public function hasItem($identifier)
    {
        return apcu_exists($identifier);
    }

    public function retrieveItem($identifier)
    {
        $data = apcu_fetch($identifier);
        return json_decode($data);
    }

    public function removeItem($identifier)
    {
        return apcu_delete($identifier);
    }
}
