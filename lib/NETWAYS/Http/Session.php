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

namespace NETWAYS\Http;

/**
 * Small class to implement a PHP session
 * @package NETWAYS\Http
 * @author Marius Hein <marius.hein@netways.de>
 */
class Session implements \ArrayAccess, \Countable
{
    /**
     * Session name
     * @var string
     */
    protected $name;

    /**
     * Path of request uri where the cookie is valid
     * @var string
     */
    protected $path;

    /**
     * Session lifetime in seconds
     * @var int
     */
    protected $lifetime = 3600;

    /**
     * Domain where the session is valid
     * @var string
     */
    protected $domain;

    /**
     * Only valid for https session
     * @var bool
     */
    protected $isSecured = false;

    /**
     * Setter for domain where the session is valid
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Getter for domain
     * @return string
     */
    public function getDomain()
    {
        if ($this->domain == 'localhost') {
            return '';
        }

        return $this->domain;
    }

    /**
     * Setter for secured flag. Session is only valid in https context
     * @param bool $isSecured
     */
    public function setIsSecured($isSecured)
    {
        $this->isSecured = $isSecured;
    }

    /**
     * Getter for is secured flag
     * @return bool
     */
    public function getIsSecured()
    {
        return $this->isSecured;
    }

    /**
     * Setter for lifetime. Lifetime is in seconds
     * @param int $lifetime
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;
    }

    /**
     * Getter for session lifetime
     * @return int
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * Setter for session name
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Getter for session name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter for session name where the session is valid
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Getter for session name
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Test method if the object is proper configured
     * @return bool
     */
    public function isConfigured()
    {
        return ($this->name && ($this->lifetime > 0) && $this->domain) ? true : false;
    }

    /**
     * Start a new session
     * @return bool
     * @throws \NETWAYS\Common\Exception
     */
    public function start()
    {
        if ($this->isConfigured() === true) {
            session_set_cookie_params(
                $this->getLifetime(),
                $this->getPath(),
                $this->getDomain(),
                $this->getIsSecured(),
                true
            );

            session_name($this->getName());
            return session_start();
        }

        throw new \NETWAYS\Common\Exception('Session is not configured');
    }

    /**
     * Regenerate session id
     */
    public function regenerateSessionId()
    {
        session_regenerate_id(true);
    }

    /**
     * Returns the current session id
     * @return string
     */
    public function getSessionId()
    {
        return session_id();
    }

    /**
     * Destroy the current session
     */
    public function destroySession()
    {
        setcookie(
            $this->getName(),
            '',
            -3600,
            $this->getPath(),
            $this->getDomain(),
            $this->getIsSecured(),
            true
        );
        session_destroy();
        unset($_SESSION);
        $_SESSION=array(); // Throw errors if not
    }

    /**
     * Write db into the session
     * @param array $data
     */
    public function write(array $data)
    {
        $_SESSION = $data;
    }

    /**
     * Return the current content of the session
     * @return mixed
     */
    public function read()
    {
        return $_SESSION;
    }

    /**
     * Count the items in the session
     * @return int
     */
    public function count()
    {
        return count(array_keys($_SESSION));
    }

    /**
     * Return single session item
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $_SESSION[$offset] = $value;
    }

    /**
     * Check if a item exists
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $_SESSION);
    }

    /**
     * Drop item from session
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($_SESSION[$offset]);
    }

    /**
     * Return session item
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return (isset($_SESSION[$offset])) ? $_SESSION[$offset] : null;
    }
}
