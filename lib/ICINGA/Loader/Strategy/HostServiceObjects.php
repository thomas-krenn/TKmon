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

namespace ICINGA\Loader\Strategy;

/**
 * Host service strategy
 *
 * Loads hosts and service in dependencies
 *
 * @package ICINGA
 * @author Marius Hein <marius.hein@netways.de>
 */
class HostServiceObjects implements \ICINGA\Interfaces\LoaderStrategyInterface
{

    /**
     * @var \NETWAYS\Common\ArrayObject
     */
    private $hosts;

    /**
     * @var \NETWAYS\Common\ArrayObject
     */
    private $services;

    public function __construct()
    {
        $this->hosts = new \NETWAYS\Common\ArrayObject();
        $this->services = new \NETWAYS\Common\ArrayObject();
    }

    public function beginLoading()
    {
        $this->hosts->clear();
        $this->services->clear();
    }

    public function finishLoading()
    {
        /** @var $service \ICINGA\Object\Service */
        $service = null;
        foreach ($this->services as $service) {
            if ($service->hostName && $this->hosts->offsetExists($service->hostName)) {
                /** @var $host \ICINGA\Object\Host */
                $host = $this->hosts->offsetGet($service->hostName);
                $host->addService($service);
            }
        }
    }

    public function newObject(\ICINGA\Object\Struct $object)
    {
        $cls = self::BASE_NAMESPACE. ucfirst($object->getObjectType());
        if (class_exists($cls)) {
            /** @var $targetObject \ICINGA\Base\Object */
            $targetObject = new $cls();
            $targetObject->fromArrayObject($object);

            if ($targetObject->getObjectName() === 'service') {
                $this->services[$targetObject->getObjectIdentifier()] = $targetObject;
            } elseif ($targetObject->getObjectName() === 'host') {
                $this->hosts[$targetObject->getObjectIdentifier()] = $targetObject;
            }
        }
    }

    public function getObjects()
    {
        return $this->hosts;
    }
}
