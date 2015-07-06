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

namespace NETWAYS\Chain\Interfaces;

/**
 * How the chain should work
 *
 * @package NETWAYS\Chain
 * @author Marius Hein <marius.hein@netways.de>
 */
interface ManagerInterface
{
    /**
     * Add a handler to chain
     *
     * @param \NETWAYS\Chain\Interfaces\HandlerInterface $handler
     * @return void
     */
    public function appendHandlerToChain(\NETWAYS\Chain\Interfaces\HandlerInterface $handler);

    /**
     * Remove handler from chain
     * @param \NETWAYS\Chain\Interfaces\HandlerInterface $handler
     * @return void
     */
    public function removeHandlerFromChain(\NETWAYS\Chain\Interfaces\HandlerInterface $handler);

    /**
     * Run the request
     * @param \NETWAYS\Chain\Interfaces\CommandInterface $command
     * @return boolean
     */
    public function processRequest(\NETWAYS\Chain\Interfaces\CommandInterface $command);

    /**
     * Configure the chain what happens on error
     * @param boolean $flag
     * @return void
     */
    public function stopOnFirstHandlerException($flag);
}
