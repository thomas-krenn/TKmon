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
 * Simple loading strategy
 *
 * Loads everything into the object
 *
 * @package ICINGA
 * @author Marius Hein <marius.hein@netways.de>
 */
class SimpleObject extends \NETWAYS\Common\ArrayObject implements \ICINGA\Interfaces\LoaderStrategyInterface
{
    public function beginLoading()
    {
        $this->clear();
    }

    public function finishLoading()
    {
        // PASS
    }

    public function newObject(\ICINGA\Object\Struct $object)
    {
        $cls = self::BASE_NAMESPACE. ucfirst($object->getObjectType());
        if (class_exists($cls)) {
            /** @var $targetObject \ICINGA\Base\Object */
            $targetObject = new $cls();
            $targetObject->fromArrayObject($object);
            $this[$targetObject->getObjectIdentifier()] = $targetObject;
        }
    }

    public function getObjects()
    {
        return $this;
    }
}
