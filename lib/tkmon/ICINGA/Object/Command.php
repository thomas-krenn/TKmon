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

namespace ICINGA\Object;

/**
 * Describes icinga commands
 *
 * @package ICINGA
 * @author Marius Hein <marius.hein@netways.de>
 *
 * @property string $commandName Unique name of command
 * @property string $commandLine Command line to execute
 *
 * @method string getCommandName() Getter for $commandName
 * @method void setCommandName(string $commandName) Setter for $commandName
 *
 * @method string getCommandLine() Getter for $commandLine
 * @method void setCommandLine(string $commandLine) Setter for $commandLine
 */
class Command extends \ICINGA\Base\Object
{

    const ARGUMENT_SEPARATOR = '!';

    /**
     * An array of argument objects
     *
     * @var \ICINGA\Base\CommandArgument[]
     */
    private $arguments = array();

    /**
     * Create a new command
     */
    public function __construct()
    {
        parent::__construct();

        $this->addAttributes(
            array(
                'command_name',
                'command_line'
            )
        );
    }

    /**
     * Setter for arguments
     * @param \ICINGA\Base\CommandArgument[] $arguments
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * Getter for arguments
     * @return \ICINGA\Base\CommandArgument[]
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Add single argument to object
     * @param \ICINGA\Base\CommandArgument $argument
     */
    public function addArgument(\ICINGA\Base\CommandArgument $argument)
    {
        $this->arguments[] = $argument;
    }

    /**
     * Returns a single argument
     * @param int $index
     * @throws \ICINGA\Exception\AttributeException
     * @return \ICINGA\Base\CommandArgument
     */
    public function getArgument($index)
    {
        if (isset($this->arguments[$index])) {
            return $this->arguments[$index];
        }

        throw new \ICINGA\Exception\AttributeException('Argument not found with index: '. $index);
    }

    /**
     * Drop argument
     * @param int $index
     * @throws \ICINGA\Exception\AttributeException
     * @return void
     */
    public function removeArgument($index)
    {
        if (isset($this->arguments[$index])) {
            unset($this->arguments[$index]);
        } else {
            throw new \ICINGA\Exception\AttributeException('Argument not found with index: '. $index);
        }
    }

    /**
     * Drop all arguments
     */
    public function purgeArguments()
    {
        $this->arguments = array();
    }

    /**
     * Sets an argument value directly
     * @param int $index index of argument item
     * @param mixed $value value to set
     */
    public function setArgumentValue($index, $value)
    {
        $this->getArgument($index)->setValue($value);
    }

    /**
     * Create a unique identifier
     *
     * If you using a tuple of objects
     *
     * @return string
     */
    public function getObjectIdentifier()
    {
        return $this->getCommandName();
    }

    /**
     * Test the object before writing
     *
     * @throws \ICINGA\Exception\ConfigException
     * @return void
     */
    public function assertObjectIsValid()
    {
        if (!$this->getCommandLine()) {
            throw new \ICINGA\Exception\ConfigException('$commandLine not set');
        }

        if (!$this->getCommandName()) {
            throw new \ICINGA\Exception\ConfigException('$commandName not set');
        }
    }

    /**
     * Get valid string for service check_command property
     */
    public function getCheckCommand()
    {
        $parts = array();

        $parts[] = $this->getCommandName();

        foreach ($this->getArguments() as $argument) {
            $parts[] = $argument->getValue();
        }

        return implode(self::ARGUMENT_SEPARATOR, $parts);
    }
}
