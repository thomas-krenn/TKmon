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

namespace ICINGA\Interfaces;

/**
 * Base strategy
 *
 * How to load objects into the loader container
 *
 * @package ICINGA
 * @author Marius Hein <marius.hein@netways.de>
 */
interface LoaderStrategyInterface
{

    /**
     * Base namespace where our concrete objects resides in
     */
    const BASE_NAMESPACE = '\\ICINGA\\Object\\';

    /**
     * Trigger when we start loading
     * @return void
     */
    public function beginLoading();

    /**
     * Trigger when we finished
     * @return void
     */
    public function finishLoading();

    /**
     * Trigger when we add a new object
     * @param \ICINGA\Object\Struct $object
     * @return void
     */
    public function newObject(\ICINGA\Object\Struct $object);

    /**
     * Getter for all objects collected
     * @return \NETWAYS\Common\ArrayObject
     */
    public function getObjects();
}
