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

namespace TKMON\Mvc\Output;

/**
 * This is a predefined json response object to handle
 * client side handling
 * @package TKMON/Mvc/Output
 * @author Marius Hein <marius.hein@netways.de>
 */
class JsonResponse extends Json
{

    const REF_TYPE_SERVER = 'server';
    const REF_TYPE_CLIENT = 'client';
    const REF_EXCEPTION = 'exception';
    const REF_UNKNOWN = 'unknown';

    const FIELD_SUCCESS = 'success';
    const FIELD_ERRORS = 'errors';
    const FIELD_DATA = 'data';

    /**
     * Creates a new object
     */
    public function __construct()
    {
        parent::__construct(
            array(
                self::FIELD_SUCCESS => false,
                self::FIELD_ERRORS => array(),
                self::FIELD_DATA => array()
            )
        );
    }

    /**
     * Setter for success flag
     * @param bool $success
     */
    public function setSuccess($success = true)
    {
        $this[self::FIELD_SUCCESS] = (bool)$success;
    }

    /**
     * Add a new error to the object
     * @param string $message
     * @param string $refType
     * @param string $ref
     */
    public function addError($message, $refType = self::REF_TYPE_SERVER, $ref = self::REF_UNKNOWN)
    {
        $this[self::FIELD_ERRORS][] = array(
            'message' => $message,
            'reftype' => $refType,
            'ref' => $ref
        );
    }

    /**
     * Adds a exception to object
     * @param \Exception $e
     */
    public function addException(\Exception $e)
    {
        $this->addError($e->getMessage(), self::REF_TYPE_SERVER, self::REF_EXCEPTION);
    }

    /**
     * Add a data row to object
     * @param mixed $data
     */
    public function addData($data)
    {
        $this[self::FIELD_DATA][] = $data;
    }

    /**
     * Set the whole data array
     * @param array $data
     */
    public function setData(array $data)
    {
        $this[self::FIELD_DATA] = $data;
    }
}
