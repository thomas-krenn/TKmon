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

namespace NETWAYS\Chain;

/**
 * Chain Manager
 *
 * Implementing the "Chain of command" in PHP style
 *
 * @package NETWAYS\Chain
 * @author Marius Hein <marius.hein@netways.de>
 */
class Manager extends \SplObjectStorage implements \NETWAYS\Chain\Interfaces\ManagerInterface
{
    private $stopOnFirstHandlerException=false;

    /**
     * Add a handler to chain
     *
     * @param \NETWAYS\Chain\Interfaces\HandlerInterface $handler
     * @return void
     */
    public function appendHandlerToChain(\NETWAYS\Chain\Interfaces\HandlerInterface $handler)
    {
        $this->attach($handler);
    }

    /**
     * Remove handler from chain
     * @param \NETWAYS\Chain\Interfaces\HandlerInterface $handler
     * @return void
     */
    public function removeHandlerFromChain(\NETWAYS\Chain\Interfaces\HandlerInterface $handler)
    {
        $this->detach($handler);
    }

    /**
     * Run the request
     * @param \NETWAYS\Chain\Interfaces\CommandInterface $command
     * @throws mixed
     * @throws \NETWAYS\Chain\Exception\HandlerException
     * @return boolean
     */
    public function processRequest(\NETWAYS\Chain\Interfaces\CommandInterface $command)
    {
        /** @var $handler \NETWAYS\Chain\Interfaces\HandlerInterface */
        $handler = null;

        $exceptions = array();

        foreach ($this as $handler) {
            try {
                $handler->processRequest($command);
            } catch (\NETWAYS\Chain\Exception\HandlerException $e) {
                if ($this->stopOnFirstHandlerException === true) {
                    throw $e;
                } else {
                    $exceptions[] = $e;
                }
            }
        }

        if (count($exceptions)) {
            $e = array_pop($exceptions);
            throw $e;
        }
    }

    /**
     * Configure the chain what happens on error
     * @param boolean $flag
     * @return void
     */
    public function stopOnFirstHandlerException($flag)
    {
        $this->stopOnFirstHandlerException = (bool)$flag;
    }


}
