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

namespace NETWAYS\Cache;

/**
 * Cache Manager
 *
 * Providing cache layer
 *
 * @package NETWAYS\Cache
 * @author Marius Hein <marius.hein@netways.de>
 */
class Manager implements \NETWAYS\Cache\Interfaces\Provider, \ArrayAccess
{
    /**
     * Provider to write to dump
     *
     * @var \NETWAYS\Cache\Interfaces\Provider
     */
    private $provider;

    /**
     * Create a new cache manager object
     *
     * @param \NETWAYS\Cache\Interfaces\Provider $provider
     */
    public function __construct(\NETWAYS\Cache\Interfaces\Provider $provider)
    {
        $this->setProvider($provider);
    }

    /**
     * Setter for provider
     *
     * @param \NETWAYS\Cache\Interfaces\Provider $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
        $this->testConfiguration();
    }

    /**
     * Getter for provider
     *
     * @return \NETWAYS\Cache\Interfaces\Provider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Test the provider
     *
     * Assert that everything is properly configured
     *
     * @throws \NETWAYS\Cache\Exception\ConfigurationErrorException
     * @return void
     */
    public function testConfiguration()
    {
        $this->getProvider()->testConfiguration();
    }


    /**
     * Stores an item on the provider
     *
     * @param mixed $item
     * @param string|null $identifier
     * @throws Exception\OperationErrorException
     * @return bool If the operation was successful
     */
    public function storeItem($item, $identifier = null)
    {
        if ($identifier === null) {
            if (is_scalar($item) === false) {
                $identifier = spl_object_hash($item);
            } else {
                throw new \NETWAYS\Cache\Exception\OperationErrorException(
                    '$item is scalar, please provide an identifier'
                );
            }
        }

        $success = $this->getProvider()->storeItem($item, $identifier);

        if ($success !== true) {
            throw new \NETWAYS\Cache\Exception\OperationErrorException('Could not store item: '. $identifier);
        }

        return $success;
    }

    /**
     * Test if an item exists on provider
     * @param string $identifier
     * @return bool
     */
    public function hasItem($identifier)
    {
        return $this->getProvider()->hasItem($identifier);
    }

    /**
     * Retrieves an item from provider
     * @param string $identifier
     * @return mixed
     */
    public function retrieveItem($identifier)
    {
        return $this->getProvider()->retrieveItem($identifier);
    }

    /**
     * Drops off an item from cache
     * @param string $identifier
     * @return bool True on success
     */
    public function removeItem($identifier)
    {
        return $this->getProvider()->removeItem($identifier);
    }


    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return $this->hasItem($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->retrieveItem($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->storeItem($value, $offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->removeItem($offset);
    }
}
