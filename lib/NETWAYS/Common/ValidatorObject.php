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

namespace NETWAYS\Common;

/**
 * Validator voyager object
 *
 * A validator is mandatory by default!
 *
 * @package NETWAYS\Common
 * @author Marius Hein <marius.hein@netways.de>
 */
class ValidatorObject
{
    /**
     * Builder to create validator objects
     *
     * @param string $field
     * @param string $humanDescription
     * @param string|int $type
     * @param int|null $flags
     * @param array|null $options
     * @return ValidatorObject
     */
    public static function create($field, $humanDescription, $type, $flags = null, $options = null)
    {
        $class = __CLASS__;

        /** @var $validator ValidatorObject */
        $validator = new $class();
        $validator->setField($field);
        $validator->setHumanDescription($humanDescription);
        $validator->setType($type);

        if ($flags) {
            $validator->setFlags($flags);
        }

        if ($options) {
            $validator->setOptions($options);
        }

        return $validator;
    }

    /**
     * Validate mandatory settings
     */
    const VALIDATE_MANDATORY    = 'mandatory';

    /**
     * Anything regex, not mandatory
     */
    const VALIDATE_ANYTHING    = 'anything';

    /**
     * Predefine regexp configurations
     * @var array
     */
    private static $regexp = array(
        self::VALIDATE_MANDATORY    => '/^.+$/',
        self::VALIDATE_ANYTHING     => '/^.*$/'
    );

    /**
     * Field name
     * @var string
     */
    private $field;

    /**
     * Note which occurs in exception
     * @var string
     */
    private $humanDescription;

    /**
     * Constant of FILTER_VALIDATE_* constants
     * @var mixed
     */
    private $type;

    /**
     * If the type internally change
     * @var mixed
     */
    private $origType;

    /**
     * Filter flags
     * @var int
     */
    private $flags;

    /**
     * Options of the validator
     * @var string[]
     */
    private $options = array();

    /**
     * Flag to indicate that field is mandatory
     * @var bool
     */
    private $mandatory = true;

    /**
     * Setter for field
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * Return the field
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set mandatory flag
     * @param bool $flag
     */
    public function setMandatory($flag = true)
    {
        $this->mandatory = (bool)$flag;
    }

    /**
     * Test if the field is mandatory
     * @return bool
     */
    public function isMandatory()
    {
        return $this->mandatory;
    }

    /**
     * Setter for flags
     * @param int $flags
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;
    }

    /**
     * Getter for flags
     * @return int
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Setter for human description
     * @param string $humanDescription
     */
    public function setHumanDescription($humanDescription)
    {
        $this->humanDescription = $humanDescription;
    }

    /**
     * Getter for human description
     * @return string
     */
    public function getHumanDescription()
    {
        return $this->humanDescription;
    }

    /**
     * Setter for type
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->setOrigType($type);

        if (($regexp = $this->getRegexFromLocalType($type)) !== null) {
            $this->type = FILTER_VALIDATE_REGEXP;
            $this->purgeOptions();
            $this->addOption('regexp', $regexp);
        } else {
            $this->type = $type;
        }
    }

    /**
     * Getter for type
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Setter for orig type
     *
     * @param mixed $origType
     */
    public function setOrigType($origType)
    {
        $this->origType = $origType;
    }

    /**
     * Getter for orig type
     *
     * @return mixed
     */
    public function getOrigType()
    {
        return $this->origType;
    }

    /**
     * Detects if we using internal validation constants
     *
     * @param string $type
     * @return null|string regexp string
     */
    private function getRegexFromLocalType($type)
    {
        if (isset(self::$regexp[$type])) {
            return self::$regexp[$type];
        }

        return null;
    }

    /**
     * Setter for options
     * @param array|string[]
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * Return options
     * @return array|string[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Purge all options
     */
    public function purgeOptions()
    {
        unset($this->options);
        $this->options = array();
    }

    /**
     * Add single option
     * @param string $name
     * @param mixed $val
     */
    public function addOption($name, $val)
    {
        $this->options[$name] = $val;
    }

    /**
     * Remove a single option
     * @param string $name
     */
    public function removeOption($name)
    {
        if ($this->optionExists($name)) {
            unset($this->options[$name]);
        }
    }

    /**
     * Test if a option exists
     * @param string $name
     * @return bool
     */
    public function optionExists($name)
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * Test if we have options added
     * @return bool
     */
    public function hasOptions()
    {
        return (count($this->getOptions()) > 0) ? true : false;
    }

    /**
     * Test if validator has flags
     *
     * @return bool
     */
    public function hasFlags()
    {
        if ($this->getFlags() !== null) {
            return true;
        }

        return false;
    }
}
