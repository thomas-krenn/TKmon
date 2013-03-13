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
 * Command argument
 *
 * @package ICINGA
 * @author Marius Hein <marius.hein@netways.de>
 */
class CommandArgument
{

    /**
     * Label
     * @var string
     */
    private $label;

    /**
     * Short description of the command part
     * @var string
     */
    private $description;

    /**
     * Validation part
     * @var string
     */
    private $validation = 'string';

    /**
     * Command part
     * @var string
     */
    private $argument;

    /**
     * Specific value
     * @var mixed
     */
    private $value;

    /**
     * Command argument builder shortcut
     * @param null|mixed $value
     * @param null|string $argument
     * @param null|string $label
     * @param null|string $description
     * @param null|string $validation
     * @return \ICINGA\Base\CommandArgument
     */
    public static function create(
        $value = null,
        $argument = null,
        $label = null,
        $description = null,
        $validation = null
    ) {
        $commandArgument = new CommandArgument();

        if ($value) {
            $commandArgument->setValue($value);
        }

        if ($argument) {
            $commandArgument->setArgument($argument);
        }

        if ($label) {
            $commandArgument->setLabel($label);
        }

        if ($description) {
            $commandArgument->setDescription($description);
        }

        if ($validation) {
            $commandArgument->setValidation($validation);
        }

        return $commandArgument;
    }

    /**
     * Builder from stdClass
     * @param \stdClass $voyager
     * @return \ICINGA\Base\CommandArgument
     */
    public static function createFromVoyager(\stdClass $voyager)
    {
        $commandArgument = new CommandArgument();

        if (isset($voyager->value)) {
            $commandArgument->setValue($voyager->value);
        }

        if (isset($voyager->argument)) {
            $commandArgument->setArgument($voyager->argument);
        }

        if (isset($voyager->label)) {
            $commandArgument->setLabel($voyager->label);
        }

        if (isset($voyager->description)) {
            $commandArgument->setDescription($voyager->description);
        }

        if (isset($voyager->validation)) {
            $commandArgument->setValidation($voyager->validation);
        }

        return $commandArgument;
    }

    /**
     * Setter for argument
     *
     * @param string $argument
     */
    public function setArgument($argument)
    {
        $this->argument = $argument;
    }

    /**
     * Getter for argument
     *
     * @return string
     */
    public function getArgument()
    {
        return $this->argument;
    }

    /**
     * Setter for description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Getter for description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Setter for label
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Getter for label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Setter for validation
     *
     * @param string $validation
     */
    public function setValidation($validation)
    {
        $this->validation = $validation;
    }

    /**
     * Getter for validation
     *
     * @return string
     */
    public function getValidation()
    {
        return $this->validation;
    }

    /**
     * Setter for argument value
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Getter for argument value
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
