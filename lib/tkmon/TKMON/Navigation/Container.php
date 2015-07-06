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

namespace TKMON\Navigation;

/**
 * Navigation container
 *
 * @package TKMON\Navigation
 * @author Marius Hein <marius.hein@netways.de>
 */
class Container extends \NETWAYS\Common\Config
{

    /**
     * The current user
     * @var \TKMON\Model\User
     */
    private $user;

    /**
     * The current action path
     * @var string
     */
    private $uri;

    /**
     * Creates a new object
     * @param \TKMON\Model\User $user
     */
    public function __construct(\TKMON\Model\User $user)
    {
        parent::__construct();
        $this->user = $user;
    }

    /**
     * Setter for current user
     * @param \TKMON\Model\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Getter for current user
     * @return \TKMON\Model\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Setter for current URI
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * Getter for current URI
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Item modifier
     * Adds css classes and attributes to nav item
     * @param \stdClass &$item
     */
    private function testMenuItem(\stdClass &$item)
    {

        // Prepare for later string handling
        $item->class = '';

        // Other header layout
        if (isset($item->type) && $item->type == 'header') {
            $item->class .= ' nav-header';
        }

        // Mark active items
        if (isset($item->href) && '/'. $item->href == $this->uri) {
            $item->active = true;
            $item->class .= ' active';
        } else {
            $item->active = false;
        }

        if (isset($item->hide)) {
            return;
        }

        // ublic visible actions
        if (isset($item->allowGuest) && $item->allowGuest === true) {
            $item->hide = false;
            return;
        }

        // Default case, user have to be authenticated
        if ($this->user->getAuthenticated() === false) {
            $item->hide = true;
            return;
        }

        $item->hide = false;
    }

    /**
     * Recursive object walk
     * @param array $items
     */
    private function walkStructure(array &$items)
    {
        foreach ($items as &$item) {
            $this->testMenuItem($item);

            if (isset($item->items) && is_array($item->items)) {
                $this->walkStructure($item->items);
            }
        }
    }

    /**
     * Returns a modified version
     * @return array|void
     */
    public function getArrayCopy()
    {
        $array = parent::getArrayCopy();

        if (is_array($array)) {
            $this->walkStructure($array);
        }

        return $array;
    }
}
