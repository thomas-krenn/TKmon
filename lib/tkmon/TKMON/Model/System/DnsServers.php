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

namespace TKMON\Model\System;

/**
 * Model to write DNS Server information
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class DnsServers extends Interfaces
{

    /**
     *
     */
    const KEY_SERVER = 'dns-nameservers';

    /**
     *
     */
    const KEY_SEARCH = 'dns-search';

    /**
     * Array of dns servers
     * @var array
     */
    private $dnsServers = array();

    /**
     * DNS Search path
     * @var string
     */
    private $dnsSearch;

    /**
     * Setter for dns search path
     * @param string $dnsSearch
     */
    public function setDnsSearch($dnsSearch)
    {
        $this->dnsSearch = $dnsSearch;
    }

    /**
     * Getter for dns search path
     * @return string
     */
    public function getDnsSearch()
    {
        return $this->dnsSearch;
    }

    /**
     * Setter of dns server array
     * @param array $dnsServers
     */
    public function setDnsServers($dnsServers)
    {
        $this->dnsServers = $dnsServers;
    }

    /**
     * Getter for dns server array
     * @return array
     */
    public function getDnsServers()
    {
        return $this->dnsServers;
    }

    /**
     * Reset dns server array
     */
    public function purgeDnsServers()
    {
        $this->dnsServers = array();
    }


    /**
     * Setter of a dns server item
     * @param int|null $index
     * @return null
     */
    public function getDnsServerItem($index = 0)
    {
        if (isset($this->dnsServers[$index])) {
                return $this->dnsServers[$index];
        }

        return null;
    }

    /**
     * Getter of dns server item
     * @param int $index
     * @param string $server
     * @return void
     */
    public function setDnsServerItem($index, $server)
    {
        $this->dnsServers[$index] = $server;
    }

    /**
     * Load the data into the object
     */
    public function load()
    {
        parent::load();

        if ($this->offsetExists(self::KEY_SEARCH)) {
            $this->setDnsSearch($this[self::KEY_SEARCH]);
        }

        if ($this->offsetExists(self::KEY_SERVER)) {
            $this->setDnsServers(explode(' ', $this[self::KEY_SERVER]));
        }
    }

    /**
     * Write the data into file
     */
    public function write()
    {
        if ($this->getDnsSearch()) {
            $this[self::KEY_SEARCH] = $this->getDnsSearch();
        } else {
            unset($this[self::KEY_SEARCH]);
        }

        if (count($this->getDnsServers())) {
            $this[self::KEY_SERVER] = implode(' ', $this->getDnsServers());
        } else {
            unset($this[self::KEY_SERVER]);
        }

        parent::write();
    }
}
