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

namespace ICINGA\Base;

/**
 * Base catalogue service
 *
 * @package ICINGA
 * @author Marius Hein <marius.hein@netways.de>
 */
abstract class Catalogue extends \NETWAYS\Chain\Manager
{

    /**
     * Create new object
     *
     * And do some configurations
     */
    public function __construct()
    {
        $this->stopOnFirstHandlerException(false);
    }

    /**
     * Query for items
     *
     * @param string $query
     * @return \NETWAYS\Common\ArrayObject
     */
    public function query($query)
    {
        $items = new \NETWAYS\Common\ArrayObject();
        $this->callCommand('query', $items, $query);
        return $items;
    }

    /**
     * Return a ready to user item from catalogue
     * @param $name
     * @throws \ICINGA\Exception\AttributeException
     * @return mixed
     */
    public function getItem($name)
    {
        $object = new \stdClass();
        $object->data = null;
        $this->callCommand('getItem', $object, $name);

        if ($object->data instanceof \stdClass) {
            $obj = clone($object->data);
            return $obj;
        }

        throw new \ICINGA\Exception\AttributeException('Object not in catalogue: '. $name);
    }

    /**
     * Call when the command chain is ready
     */
    public function makeReady()
    {
        $this->callCommand('initialize');
    }
}
