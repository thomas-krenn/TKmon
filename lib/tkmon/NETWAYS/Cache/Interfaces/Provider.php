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
 * @copyright 2012-2014 NETWAYS GmbH <info@netways.de>
 */

namespace NETWAYS\Cache\Interfaces;

/**
 * Provider interface
 *
 * @package NETWAYS\Cache
 * @author Marius Hein <marius.hein@netways.de>
 */
interface Provider
{
    /**
     * Test the provider
     *
     * Assert that everything is properly configured
     *
     * @throws \NETWAYS\Cache\Exception\ConfigurationErrorException
     * @return void
     */
    public function testConfiguration();

    /**
     * Stores an item on the provider
     * @param mixed $item
     * @param string|null $identifier
     * @return bool If the operation was successful
     */
    public function storeItem($item, $identifier = null);

    /**
     * Test if an item exists on provider
     * @param string $identifier
     * @return bool
     */
    public function hasItem($identifier);

    /**
     * Retrieves an item from provider
     * @param string $identifier
     * @return mixed
     */
    public function retrieveItem($identifier);

    /**
     * Drops off an item from cache
     * @param string $identifier
     * @return bool True on success
     */
    public function removeItem($identifier);
}
