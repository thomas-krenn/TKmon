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
     * @param string $field Field within the array
     * @param string $humanType Description which occurs in exception/error text
     * @param $type PHP VALIDATION_FILTER constant
     * @param null $flags PHP FILTER flags
     * @param null $options Option array based on validation type
     */
    public function addValidator($field, $humanType, $type, $flags = null, $options = null)
    {
        $filter = new \stdClass();
        $filter->type = $type;
        $filter->humanType = $humanType;
        $filter->flags = $flags;
        $filter->options = $options;
        $filter->field = $field;

        $this[$field] = $filter;
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
        $return = true;
        foreach ($iterator as $field => $val) {
            if ($this->offsetExists($field)) {
                $validator = $this[$field];
                $test = $this->validateValue($validator, $val);

                if ($test === false && $return === true) {
                    $return = false;
                }
            }
        }

        return $return;
    }

    /**
     * Single validator
     * @param \stdClass $validator
     * @param mixed $value
     * @return bool
     * @throws Exception\ValidatorException
     */
    private function validateValue(\stdClass $validator, $value)
    {
        $options = array();
        if (isset($validator->options)) {
            $options['options'] = $validator->options;
        }

        if (isset($validator->flags)) {
            $options['flags'] = $validator->flags;
        }

        $return = filter_var($value, $validator->type, $options);

        if ($return !== $value) {
            if ($this->throwException === true) {
                throw new \NETWAYS\Common\Exception\ValidatorException(
                    'Validation of field '
                    . $validator->field
                    . ' fails. ('
                    . $validator->humanType
                    . ')'
                );
            }

            return false;
        }

        return true;
    }
}
