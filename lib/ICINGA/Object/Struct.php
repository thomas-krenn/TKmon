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

namespace ICINGA\Object;

/**
 * Struct to move data arround
 *
 * Voyager object with unknown properties
 *
 * @package ICINGA
 * @author Marius Hein <marius.hein@netways.de>
 */
class Struct extends \NETWAYS\Common\ArrayObject
{
    /**
     * Object type
     * @var string
     */
    private $objectType;

    /**
     * Setter for object type
     * @param string $objectType
     */
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;
    }

    /**
     * Getter for object type
     * @return string
     */
    public function getObjectType()
    {
        return $this->objectType;
    }
}
