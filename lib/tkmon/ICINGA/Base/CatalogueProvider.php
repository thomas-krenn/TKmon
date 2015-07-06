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

namespace ICINGA\Base;

/**
 * Base catalogue data provider
 *
 * @package ICINGA
 * @author Marius Hein <marius.hein@netways.de>
 */
abstract class CatalogueProvider extends \NETWAYS\Chain\ReflectionHandler
{
    /**
     * Initialize the provider
     *
     * - make ready
     * - throw errors
     *
     * @return void
     */
    abstract public function commandInitialize();

    /**
     * Query for items in this catalogue
     * @param \NETWAYS\Common\ArrayObject $result
     * @param string $query
     * @return void
     */
    abstract public function commandQuery(\NETWAYS\Common\ArrayObject $result, $query);

    /**
     * Return a ready to use item
     * @param \stdClass $voyager
     * @param string $name unique item id
     * @return void
     */
    abstract public function commandGetItem(\stdClass $voyager, $name);
}
