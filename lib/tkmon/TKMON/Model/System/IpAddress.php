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
 * @copyright 2012-2014 NETWAYS GmbH <info@netways.de>
 */

namespace TKMON\Model\System;
use TKMON\Model\System\Exception\IpAddressException;

/**
 * Class to handle IP address configuration
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class IpAddress extends Interfaces
{

    /**
     * INET type flag
     */
    const TYPE_STATIC = 'static';

    /**
     * INET type flag
     */
    const TYPE_DHCP = 'dhcp';

    /**
     * Field address
     */
    const FIELD_ADDRESS = 'address';

    /**
     * Field netmask
     */
    const FIELD_NETMASK = 'netmask';

    /**
     * Field gateway
     */
    const FIELD_GATEWAY = 'gateway';

    /**
     * Ip address
     * @var string
     */
    private $ipAddress;

    /**
     * Network subnet
     * @var string
     */
    private $ipNetmask;

    /**
     * Gateway address
     * @var string
     */
    private $ipGateway;

    /**
     * Interface configuration (dhcp/static)
     * @var string
     */
    private $configType = self::TYPE_STATIC;

    /**
     * Setter for config type
     * @param $configType
     */
    public function setConfigType($configType)
    {
        $this->configType = $configType;
    }

    /**
     * Getter for config type
     * @return string
     */
    public function getConfigType()
    {
        return $this->configType;
    }

    /**
     * Setter for ip address
     * @param $ipAddress
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }

    /**
     * Getter for ip address
     * @return mixed
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * Setter for gateway
     * @param $ipGateway
     */
    public function setIpGateway($ipGateway)
    {
        $this->ipGateway = $ipGateway;
    }

    /**
     * Getter for gateway
     * @return mixed
     */
    public function getIpGateway()
    {
        return $this->ipGateway;
    }

    /**
     * Setter for netmask
     * @param $ipNetmask
     */
    public function setIpNetmask($ipNetmask)
    {
        $this->ipNetmask = $ipNetmask;
    }

    /**
     * Getter for netmask
     * @return mixed
     */
    public function getIpNetmask()
    {
        return $this->ipNetmask;
    }

    /**
     * Load and prepare object
     */
    public function load()
    {
        parent::load();

        if ($this->offsetExists(self::FIELD_ADDRESS)) {
            $this->setIpAddress($this[self::FIELD_ADDRESS]);
        }

        if ($this->offsetExists(self::FIELD_NETMASK)) {
            $this->setIpNetmask($this[self::FIELD_NETMASK]);
        }

        if ($this->offsetExists(self::FIELD_GATEWAY)) {
            $this->setIpGateway($this[self::FIELD_GATEWAY]);
        }

        if ($this->hasFlag(self::TYPE_DHCP)) {
            $this->setConfigType(self::TYPE_DHCP);
        } elseif ($this->hasFlag(self::TYPE_STATIC)) {
            $this->setConfigType(self::TYPE_STATIC);
        } else {
            throw new IpAddressException("Could not detect config type (dhcp/static)");
        }
    }

    /**
     * Prepare object and write
     * @throws IpAddressException
     */
    public function write()
    {
        if ($this->getConfigType() === self::TYPE_STATIC) {
            // Static network configuration, prepare all fields

            if (!$this->getIpAddress()) {
                throw new IpAddressException('Ip address is missing!');
            }

            if (!$this->getIpGateway()) {
                throw new IpAddressException('Gateway is missing');
            }

            if (!$this->getIpNetmask()) {
                throw new IpAddressException('Netmask is missing');
            }

            $this[self::FIELD_ADDRESS] = $this->getIpAddress();
            $this[self::FIELD_GATEWAY] = $this->getIpGateway();
            $this[self::FIELD_NETMASK] = $this->getIpNetmask();

            $this->purgeFlags();
            $this->setFlag(self::TYPE_STATIC);

        } elseif ($this->getConfigType() === self::TYPE_DHCP) {
            $this->offsetUnset(self::FIELD_ADDRESS);
            $this->offsetUnset(self::FIELD_GATEWAY);
            $this->offsetUnset(self::FIELD_NETMASK);
            $this->purgeFlags();
            $this->setFlag(self::TYPE_DHCP);

        } else {
            throw new IpAddressException('Unknown type: '. $this->getConfigType());
        }

        parent::write();
    }
}
