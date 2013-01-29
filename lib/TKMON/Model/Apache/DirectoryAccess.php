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

namespace TKMON\Model\Apache;

/**
 * Model to write password files
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class DirectoryAccess extends \TKMON\Model\ApplicationModel
{

    /**
     * Temp prefix
     */
    const TEMP_PREFIX = 'tkmon-apache-access';

    /**
     * Security setting Order Allow,Deny
     */
    const ORDER_ALLOW_DENY = 'Allow,Deny';

    /**
     * Security setting Order Deny,Allow
     */
    const ORDER_DENY_ALLOW = 'Deny,Allow';

    /**
     * Access ordering
     * @var string
     */
    private $order;

    /**
     * Access restriction
     * @var string
     */
    private $from;

    /**
     * File to parse / rewrite
     * @var string
     */
    private $file;

    /**
     * Setter for from network
     * @param string $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * Getter for from
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Setter for access order
     * @param string $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * Getter for access order
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Modify access to localhost only
     */
    public function allowLocalhostOnly()
    {
        $this->setFrom('localhost');
        $this->setOrder(self::ORDER_DENY_ALLOW);
    }

    /**
     * Modify access to allow everybody
     */
    public function allowAll()
    {
        $this->setFrom('all');
        $this->setOrder(self::ORDER_ALLOW_DENY);
    }

    /**
     * Setter for file to rewrite
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * Setter for file
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Rewrites the file
     */
    public function rewrite()
    {

    }
}
