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

namespace NETWAYS\Chain;

/**
 * Implement commands with classes
 *
 * @package NETWAYS\Chain
 * @author Marius Hein <marius.hein@netways.de>
 */
abstract class ReflectionHandler implements \NETWAYS\Chain\Interfaces\HandlerInterface
{

    /**
     * Prefix of public methods to cal
     */
    const COMMAND_METHOD_PREFIX = 'command';

    /**
     * Object reflector
     * @var \ReflectionObject
     */
    private $reflection;

    /**
     * Called when a command was called
     * @param Interfaces\CommandInterface $command
     */
    public function processRequest(\NETWAYS\Chain\Interfaces\CommandInterface $command)
    {
        if (!$this->reflection instanceof \ReflectionObject) {
            $this->reflection = new \ReflectionObject($this);
        }

        $methodName = self::COMMAND_METHOD_PREFIX. ucfirst($command->getCommandName());

        if ($this->reflection->hasMethod($methodName)) {

            /** @var $reflectionMethod \ReflectionMethod */
            $reflectionMethod = $this->reflection->getMethod($methodName);
            $reflectionMethod->invokeArgs($this, $command->getArguments());
        }
    }
}
