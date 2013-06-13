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
 * Extended implementation ArrayObject
 *
 * @package NETWAYS\Common
 * @author Marius Hein <marius.hein@netways.de>
 */
class ArrayObjectValidator extends ArrayObject
{

    /**
     * Validate mandatory settings
     */
    const VALIDATE_MANDATORY    = 'mandatory';

    /**
     * Flag to throw an exception on validation error
     * @var bool
     */
    private $throwException = true;

    /**
     * Configure function for throwException flag
     * @param bool $flag
     */
    public function throwOnErrors($flag = true)
    {
        $this->throwException = $flag;
    }

    /**
     * Adds a new validator
     *
     * @deprecated
     * @param string $field Field within the array
     * @param string $humanType Description which occurs in exception/error text
     * @param $type PHP VALIDATION_FILTER constant
     * @param null $flags PHP FILTER flags
     * @param null $options Option array based on validation type
     */
    public function addValidator($field, $humanType, $type, $flags = null, $options = null)
    {
        $validator = ValidatorObject::create($field, $humanType, $type, $flags, $options);
        $this->addValidatorObject($validator);
    }

    /**
     * Add validator object to validator
     * 
     * @param ValidatorObject $object
     */
    public function addValidatorObject(ValidatorObject $object)
    {
        $this[$object->getField()] = $object;
    }

    /**
     * Test the values on the array
     *
     * @param \ArrayObject $object
     * @return bool
     */
    public function validateArrayObject(\ArrayObject $object)
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($object));
        $got = array();

        /*
         * Testing existing values
         */

        $return = true;
        foreach ($iterator as $field => $val) {
            if ($this->offsetExists($field)) {

                /** @var $validator ValidatorObject */
                $validator = $this[$field];

                $got[] = $validator;

                $test = $this->validateValue($validator, $val);

                if ($test === false && $return === true) {
                    $return = false;
                }
            }
        }

        /*
         * Testing mandatory objects
         */
        foreach ($this as $validator) {
            if (in_array($validator, $got) === false) {
                $this->validateMandatory($validator);
            }
        }

        return $return;
    }

    /**
     * Validate mandatory fields
     * @param ValidatorObject $validator
     * @throws Exception\ValidatorException
     */
    private function validateMandatory(\NETWAYS\Common\ValidatorObject $validator)
    {
        if ($validator->isMandatory()) {
            if ($this->throwException === true) {
                throw new \NETWAYS\Common\Exception\ValidatorException(
                    'Validation of field '
                    . $validator->getField()
                    . ' failed ('
                    . $validator->getHumanDescription()
                    . '). This field is mandatory'
                );
            }
        }
    }

    /**
     * Single validator
     * @param \NETWAYS\Common\ValidatorObject $validator
     * @param mixed $value
     * @return bool
     * @throws Exception\ValidatorException
     */
    private function validateValue(\NETWAYS\Common\ValidatorObject $validator, $value)
    {
        $options = array();
        if (is_bool($value) === false && !$value && $validator->isMandatory() === false) {
            return true;
        }

        if ($validator->hasOptions()) {
            $options['options'] = $validator->getOptions();
        }

        if ($validator->hasFlags()) {
            $options['flags'] = $validator->getFlags();
        }

        $return = filter_var($value, $validator->getType(), $options);

        if ($return !== $value) {
            if ($this->throwException === true) {
                throw new \NETWAYS\Common\Exception\ValidatorException(
                    'Validation of field '
                    . $validator->getField()
                    . ' failed. ('
                    . $validator->getHumanDescription()
                    . ')'
                );
            }

            return false;
        }

        return true;
    }
}
