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

namespace NETWAYS\Crypt;

/**
 * Class UniqueId
 *
 * Unique id number generator with some essential testing
 *
 * @package NETWAYS\Crypt
 * @author Marius Hein <marius.hein@netways.de>
 */
class UniqueId
{
    /**
     * Default length of bytes
     */
    const DEFAULT_LENGTH = 64;

    /**
     * Length of token in bytes
     * @var int
     */
    private $length = self::DEFAULT_LENGTH;

    /**
     * Tests for strong algorithm
     * @var bool
     */
    private $assertStrongAlgorithm = true;

    /**
     * Creates a new generator
     * @param null $length
     */
    public function __construct($length = null)
    {
        if ($length !== null) {
            $this->setLength($length);
        }
    }

    /**
     * Setter for $length
     *
     * @param int $length
     * @throws \InvalidArgumentException
     */
    public function setLength($length)
    {
        if (is_int($length) === false) {
            throw new \InvalidArgumentException('$length is not an integer: '. $length);
        }

        $this->length = $length;
    }

    /**
     * Getter for $length
     *
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Set assertion flag for strong cryptographic algorithm
     *
     * @param boolean $bool
     */
    public function assertStrongAlgorithm($bool)
    {
        $this->assertStrongAlgorithm = (boolean)$bool;
    }

    public function generateToken($asString = true)
    {
        if (!function_exists('openssl_random_pseudo_bytes')) {
            throw new \RuntimeException('openssl_random_pseudo_bytes is not available on system');
        }

        $bool = false;

        $bytes = openssl_random_pseudo_bytes($this->getLength(), $bool);

        if ($this->assertStrongAlgorithm === true and $bool === false) {
            throw new \RuntimeException('No strong algorithm used to generate salt');
        }

        if ($asString === true) {
            return bin2hex($bytes);
        }

        return $bytes;
    }

    public function __toString()
    {
        return $this->generateToken();
    }
}