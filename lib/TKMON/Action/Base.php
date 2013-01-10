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

namespace TKMON\Action;

/**
 * Base class for frontend actions
 *
 * @package TKMON\Action
 * @author Marius Hein <marius.hein@netways.de>
 */
abstract class Base
{

    /**
     * Our DI container
     * @var \Pimple
     */
    protected $container;

    /**
     * Setter for DI container
     * @param \Pimple $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * Getter for DI container
     * @return \Pimple
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Return the parameter holder
     * @return \NETWAYS\Http\CgiParams
     */
    protected function getParameterHolder()
    {
        return $this->container['params'];
    }

    /**
     * Initialize method after action is configured
     */
    public function init()
    {
        // DO NOTHING HERE
    }
}
