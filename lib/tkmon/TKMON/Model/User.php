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

use NETWAYS\Http\Session;
use TKMON\Exception\ModelException;
use TKMON\Exception\UserException;
use TKMON\Model\Apache\PasswordFile;

/**
 * This is the current user
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class User extends ApplicationModel
{

    /**
     * Hashing algo for passwords
     *
     * SHA512 is more secure:
     * https://devops.netways.de/issues/2514
     */
    const HASH_ALGO = 'sha512';

    /**
     * Session id for user locale
     */
    const NS_LOCALE = 'user.locale';

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
        /** @var Session $session */
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
        /** @var \PDO $db */
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
     * @throws UserException
     */
    public function doAuthenticate($username, $password)
    {

        if (!$username) {
            throw new UserException('Username is mandatory.');
        }

        if (!$password) {
            throw new UserException('Password is mandatory.');
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

        throw new UserException('Could not authenticate user: ' . $username);
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
     * @throws UserException
     * @param string $newPassword
     * @return string
     */
    public function changePassword($currentPassword, $newPassword, $verification)
    {

        if ($this->getAuthenticated()===false || !$this->getId()) {
            throw new UserException('User not initialized and authenticated');
        }

        if (!$currentPassword) {
            throw new UserException('Current password is mandatory');
        }

        if (!$newPassword) {
            throw new UserException('New password is mandatory');
        }

        if (!$verification) {
            throw new UserException('Verification is mandatory');
        }

        if ($newPassword !== $verification) {
            throw new UserException('Passwords do not match');
        }

        if ($this->testCurrentPassword($currentPassword) === false) {
            throw new UserException('Your current password is wrong');
        }

        if ($currentPassword === $newPassword) {
            throw new UserException('Old and new password are the same');
        }

        $data = $this->getUserData($this->getId());

        // Change on system level
        $this->changeSystemPassword($data[self::FIELD_NAME], $newPassword);

        // Change icinga access
        $this->changeIcingaPassword($newPassword);

        $newSalt = $this->generateSalt();
        $newHash = hash_hmac(self::HASH_ALGO, $newPassword, $newSalt);

        /** @var \PDO $db */
        $db = $this->container['db'];
        $statement = $db->prepare('UPDATE user SET password=:password, salt=:salt WHERE ID=:id;');
        $statement->bindValue(':password', $newHash, \PDO::PARAM_STR);
        $statement->bindValue(':salt', $newSalt, \PDO::PARAM_STR);
        $statement->bindValue(':id', $this->getId(), \PDO::PARAM_INT);

        return $statement->execute();
    }

    /**
     * Generate new secure salt
     * @return string
     */
    private function generateSalt()
    {
        return uniqid(mt_rand(), true);
    }

    /**
     * Change the http password of icinga admin
     * @param string $password
     */
    private function changeIcingaPassword($password)
    {
        $icingaUser = $this->container['config']->get('icinga.adminuser', 'icingaadmin');
        $passwdFile = $this->container['config']->get('icinga.passwdfile');

        $passwdModel = new PasswordFile($this->container);
        $passwdModel->setPasswordFile($passwdFile);
        $passwdModel->load();
        $passwdModel->addUser($icingaUser, $password);
        $passwdModel->write();
    }

    /**
     * Change password on system
     *
     * Use with caution: No validation here!
     *
     * @param string $username
     * @param string $password
     */
    private function changeSystemPassword($username, $password)
    {
        // Store for later use
        $flag = $this->getSystemAccess();

        $command = $this->container['command']->create('chpasswd');
        $command->setInput($username. ':'. $password);

        $command->execute();

        // Set system access flag again (see https://devops.netways.de/issues/2512)
        $this->controlSystemAccess($flag);
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

    /**
     * Control system access for that user
     * @param boolean $flag
     */
    public function controlSystemAccess($flag)
    {
        $command = $this->container['command']->create('usermod');
        $command->addPositionalArgument($this->getName());

        if ($flag === true) {
            $command->addNamedArgument('--unlock');
        } elseif ($flag === false) {
            $command->addNamedArgument('--lock');
        }

        $command->execute();
    }

    /**
     * Getter for system access status
     * @return bool
     */
    public function getSystemAccess()
    {
        $command = $this->container['command']->create('passwd');
        $command->addNamedArgument('--status');
        $command->addPositionalArgument($this->container['user']->getName());
        $command->execute();

        $data = explode(' ', $command->getOutput());

        if ($data[1] === 'P') {
            return true;
        }

        return false;
    }

    /**
     * Getter function for locale
     *
     * Try to determine current locale configured:
     *
     * - session
     * - configuration
     * - default
     *
     * @return string|null locale name e.g. de_DE
     * @throws ModelException
     */
    public function getLocale()
    {
        /** @var $session \NETWAYS\Http\Session */
        $session = $this->container['session'];

        /** @var $config \NETWAYS\Common\Config */
        $config = $this->container['config'];

        $locale = $session[self::NS_LOCALE];

        if (!$locale) {
            $locale = $config->get('locale.name');
        }

        if (!$locale) {
            throw new ModelException('Locale not properly configured');
        }

        return $locale;
    }

    /**
     * Sets the current user locale
     *
     * And write them into session
     *
     * @param string $locale locale name e.g. de_DE
     */
    public function setLocale($locale)
    {
        /** @var $session \NETWAYS\Http\Session */
        $session = $this->container['session'];

        /** @var $intl \NETWAYS\Intl\Gettext */
        $intl = $this->container['intl'];

        $intl->setLocale($locale);
        $session[self::NS_LOCALE] = $locale;
    }
}
