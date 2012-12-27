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

namespace TKMON\Model;

/**
 * This is the current user
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class User
{

    const HASH_ALGO = 'md5';

    /**
     * Session id for authenticated flag
     */
    const NS_AUTHENTICATED = 'user.authenticated';

    /**
     * Session id for id flag
     */
    const NS_USERID = 'user.id';

    /**
     * Field tag id
     */
    const FIELD_ID = 'id';

    /**
     * Field tag name
     */
    const FIELD_NAME = 'name';

    /**
     * Field tag password
     */
    const FIELD_PASSWORD = 'password';

    /**
     * Field tag salt
     */
    const FIELD_SALT = 'salt';

    /**
     * DI container
     * @var \Pimple
     */
    protected $container;
    /**
     * Flag if the user if authenticated
     * @var bool
     */
    protected $authenticated = false;

    /**
     * Name of the user (loginname)
     * @var string
     */
    protected $name;

    /**
     * User id in database
     * @var int
     */
    protected $id;

    /**
     * Creates a new user
     *
     * @param \Pimple $container
     */
    public function __construct(\Pimple $container)
    {
        $this->container = $container;
    }

    /**
     * Setter for authenticated flag
     * @param bool $authenticated
     */
    public function setAuthenticated($authenticated)
    {
        $this->authenticated = $authenticated;
    }

    /**
     * Getter for authenticated flag
     * @return bool
     */
    public function getAuthenticated()
    {
        return $this->authenticated;
    }

    /**
     * Setter for DI container
     * @param \Pimple $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * Getter for DI container
     * @return \Pimple
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Setter for id
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Getter for id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Setter for name
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Getter for name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Initialize the user
     *
     * Checks if we have a current session and loads userdata
     * from session into the object. If no valid session there
     * we create one for you with context 'guest'
     */
    public function initialize()
    {
        $session = $this->container['session'];
        if ($session->offsetExists(self::NS_AUTHENTICATED)) {
            $this->setAuthenticated((bool)$session->offsetGet(self::NS_AUTHENTICATED));
        }

        if ($session->offsetExists(self::NS_USERID) && $session[self::NS_USERID] > 0) {
            $data = $this->getUserData($session[self::NS_USERID]);
            $this->applyDataToObject($data);
        }

        if (!$this->id) {
            $this->setId(0);
            $this->setAuthenticated(false);
            $this->setName('Guest');
        }
    }

    /**
     * Write data to session object
     */
    public function write()
    {
        $session = $this->container['session'];
        $session[self::NS_USERID] = $this->getId();
        $session[self::NS_AUTHENTICATED] = $this->getAuthenticated();
    }

    /**
     * Return database row as as annay
     * @param string $fieldValue fieldContent
     * @param string $fieldName Database field
     * @return bool|array
     */
    private function getUserData($fieldValue, $fieldName = self::FIELD_ID)
    {
        $db = $this->container['db'];
        $statement = $db->prepare(
            'SELECT * from user where '
            . $fieldName . '=:value LIMIT 1;'
        );

        $statement->bindValue(':value', $fieldValue, \PDO::PARAM_STR);
        $re = $statement->execute();

        if ($re) {
            return $statement->fetch();
        }

        return false;
    }

    /**
     * Apply data from array to the object
     * @param array $data
     */
    private function applyDataToObject(array $data)
    {
        $this->setId($data[self::FIELD_ID]);
        $this->setName($data[self::FIELD_NAME]);
    }

    /**
     * Tries to authenticate
     * @param string $username
     * @param string $password
     * @throws \TKMON\Exception\UserException
     */
    public function doAuthenticate($username, $password)
    {

        if (!$username) {
            throw new \TKMON\Exception\UserException('Username is mandatory.');
        }

        if (!$password) {
            throw new \TKMON\Exception\UserException('Password is mandatory.');
        }

        $data = $this->getUserData($username, self::FIELD_NAME);

        if (is_array($data) === true) {
            $check_password = hash_hmac(self::HASH_ALGO, $password, $data[self::FIELD_SALT]);

            if ($this->testPassword(
                $password,
                $data[self::FIELD_PASSWORD],
                $data[self::FIELD_SALT]
            ) === true) {
                $this->setAuthenticated(true);
                $this->applyDataToObject($data);
                $this->write();
                return;
            }
        }

        throw new \TKMON\Exception\UserException('Could not authenticate user: ' . $username);
    }

    /**
     * Tests current user password
     * @param string $password
     * @return bool Success or not
     */
    public function testCurrentPassword($password)
    {
        $data = $this->getUserData($this->getId());
        if (is_array($data)) {
            return $this->testPassword(
                $password,
                $data[self::FIELD_PASSWORD],
                $data[self::FIELD_SALT]
            );
        }

        return false;
    }

    /**
     * Change the password
     *
     * @param string $currentPassword
     * @param string $newPassword
     * @param string $verification
     * @return bool
     * @throws \TKMON\Exception\UserException
     */
    public function changePassword($currentPassword, $newPassword, $verification)
    {

        if ($this->getAuthenticated()===false || !$this->getId()) {
            throw new \TKMON\Exception\UserException('User not initialized and authenticated');
        }

        if (!$currentPassword) {
            throw new \TKMON\Exception\UserException('Current password is mandatory');
        }

        if (!$newPassword) {
            throw new \TKMON\Exception\UserException('New password is mandatory');
        }

        if (!$verification) {
            throw new \TKMON\Exception\UserException('Verification is mandatory');
        }

        if ($newPassword !== $verification) {
            throw new \TKMON\Exception\UserException('Password does not match');
        }

        if ($this->testCurrentPassword($currentPassword) === false) {
            throw new \TKMON\Exception\UserException('Your current password is wrong');
        }

        if ($currentPassword === $newPassword) {
            throw new \TKMON\Exception\UserException('Old and new password are the same!');
        }

        $data = $this->getUserData($this->getId());
        $newHash = hash_hmac(self::HASH_ALGO, $newPassword, $data[self::FIELD_SALT]);

        $db = $this->container['db'];
        $statement = $db->prepare('UPDATE user SET password=:password WHERE ID=:id;');
        $statement->bindValue(':password', $newHash, \PDO::PARAM_STR);
        $statement->bindValue(':id', $this->getId(), \PDO::PARAM_INT);

        return $statement->execute();
    }

    /**
     * Class wide password teste method
     *
     * @param string $testPassword
     * @param string $passwordHash
     * @param string $passwordSalt
     * @return bool Success or not
     */
    private function testPassword($testPassword, $passwordHash, $passwordSalt)
    {
        $check = hash_hmac(self::HASH_ALGO, $testPassword, $passwordSalt);

        if ($passwordHash === $check) {
            return true;
        }

        return false;
    }
}
