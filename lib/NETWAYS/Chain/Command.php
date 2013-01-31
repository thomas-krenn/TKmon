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
 * Command
 *
 * @package NETWAYS\Chain
 * @author Marius Hein <marius.hein@netways.de>
 */
class Command extends \NETWAYS\Common\ArrayObject implements \NETWAYS\Chain\Interfaces\CommandInterface
{
    /**
     * Name of command
     *
     * @var string
     */
    private $commandName;

    /**
     * Creates a new command object
     * @param string $commandName
     */
    public function __construct($commandName)
    {
        parent::__construct();
        $this->setCommandName($commandName);
    }

    /**
     * Setter for command name
     * @param string $name
     * @return void
     */
    public function setCommandName($name)
    {
        $this->commandName = $name;
    }

    /**
     * Getter for command name
     * @return string
     */
    public function getCommandName()
    {
        return $this->commandName;
    }

    /**
     * Return an array of all arguments
     * @return array
     */
    public function getArguments()
    {
        $out = array();
        foreach ($this as $key=>&$val) {
            $out[$key] =& $val;
        }
        return $out;
    }


}
