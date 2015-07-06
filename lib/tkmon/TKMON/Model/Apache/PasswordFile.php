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

namespace TKMON\Model\Apache;

/**
 * Model to write password files
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class PasswordFile extends \NETWAYS\Common\ArrayObject implements \TKMON\Interfaces\ApplicationModelInterface
{

    /**
     * Temp file prefix
     */
    const TEMP_PREFIX = 'tkmon-htpasswd';

    /**
     * Password file
     * @var string
     */
    private $passwordFile;

    /**
     * Array of users
     * @var array
     */
    private $users = array();

    /**
     * DI container
     * @var \Pimple
     */
    private $container;

    /**
     * Create new object
     * @param \Pimple $container
     */
    public function __construct(\Pimple $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    /**
     * Setter for DI container
     * @param \Pimple $container
     */
    public function setContainer(\Pimple $container)
    {
        $this->container = $container;
    }

    /**
     * Getter for DI configuration
     * @return \Pimple
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Setter for password file
     * @param $passwordFile
     */
    public function setPasswordFile($passwordFile)
    {
        $this->passwordFile = $passwordFile;
    }

    /**
     * Getter for password file
     * @return string
     */
    public function getPasswordFile()
    {
        return $this->passwordFile;
    }

    /**
     * Add user to struct
     *
     * Password will not converted to APR format
     *
     * @param $username
     * @param $encryptedPassword
     */
    private function addUserRaw($username, $encryptedPassword)
    {
        $this[$username] = $encryptedPassword;
    }

    /**
     * Adds or changes a user
     *
     * Password will be converted to Apache APR hash format
     *
     * @param $username
     * @param $plainPassword
     */
    public function addUser($username, $plainPassword)
    {
        $encrypted = $this->encryptPassword($username, $plainPassword);
        $this->addUserRaw($username, $encrypted);
    }

    /**
     * Encrypt password
     *
     * Call htpasswd to do this
     *
     * @param $username
     * @param $plainPassword
     * @return mixed
     * @throws \TKMON\Exception\ModelException
     */
    private function encryptPassword($username, $plainPassword)
    {
        /** @var $command \NETWAYS\IO\Process */
        $command = $this->container['command']->create('htpasswd');
        $command->addNamedArgument('-n');
        $command->addNamedArgument('-b');
        $command->addPositionalArgument($username);
        $command->addPositionalArgument($plainPassword);
        $command->execute();

        $string = trim($command->getOutput());
        list($test, $password) = explode(':', $string);

        if ($test === $username) {
            return $password;
        }

        throw new \TKMON\Exception\ModelException('Username consistency check failed');
    }

    /**
     * Assertion for existing file
     *
     * @throws \TKMON\Exception\ModelException
     */
    private function assertPasswordFileExists()
    {
        if (!file_exists($this->getPasswordFile())) {
            throw new \TKMON\Exception\ModelException("Password file does not exist");
        }
    }

    /**
     * Load data into object
     */
    public function load()
    {
        $this->assertPasswordFileExists();

        $fo = new \SplFileObject($this->getPasswordFile());

        foreach ($fo as $line) {
            if ($line) {
                $parts = explode(':', $line, 2);
                $this->addUserRaw($parts[0], trim($parts[1]));
            }
        }
    }

    /**
     * Writes data from object to file with sudo rights
     * @throws \TKMON\Exception\ModelException
     */
    public function write()
    {
        if (!$this->count()) {
            throw new \TKMON\Exception\ModelException("Nothing to write");
        }

        $fo = new \NETWAYS\IO\RealTempFileObject(self::TEMP_PREFIX, 'w');

        foreach ($this as $user => $password) {
            $fo->fwrite($user. ':'. $password. PHP_EOL);
        }

        $fo->fflush();

        /** @var $mv \NETWAYS\IO\Process */
        $mv = $this->container['command']->create('mv');
        $mv->addPositionalArgument($fo->getRealPath());
        $mv->addPositionalArgument($this->getPasswordFile());
        $mv->execute();

        unset($fo);
    }
}
