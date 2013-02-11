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

namespace TKMON\Model\Database;

/**
 * Create PDOConnections from debconf php files
 *
 * @package TKMON/Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class DebConfBuilder
{
    const TYPE_SQLITE = 'sqlite';

    private static $setterMap = array(
        'dbuser'    => 'setUser',
        'dbpass'    => 'setPassword',
        'basepath'  => 'setBasePath',
        'dbname'    => 'setName',
        'dbserver'  => 'setServer',
        'dbport'    => 'setPort',
        'dbtype'    => 'setType'
    );

    private static $connectionMap = array(
        self::TYPE_SQLITE => 'createSqLiteConnection'
    );

    private static $defaultOptions = array(
        \PDO::ATTR_PERSISTENT           => true,
        \PDO::ATTR_ERRMODE              => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_CASE                 => \PDO::CASE_LOWER,
        \PDO::ATTR_DEFAULT_FETCH_MODE   => \PDO::FETCH_ASSOC
    );

    private $user;

    private $password;

    private $basePath;

    private $name;

    private $server;

    private $port;

    private $type;

    /**
     * @var \PDO
     */
    private $connection;

    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPort($port)
    {
        $this->port = $port;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setServer($server)
    {
        $this->server = $server;
    }

    public function getServer()
    {
        return $this->server;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    /**
     * Loads php configuratino info object
     *
     * @param string $file deb conf generated php configuration
     * @throws \TKMON\Exception\ModelException
     */
    public function loadFromFile($file)
    {
        if (!file_exists($file)) {
            throw new \TKMON\Exception\ModelException('File does not exist: '. $file);
        }

        require $file;

        foreach(self::$setterMap as $var => $setter) {
            if (isset(${$var})) {
                $this->$setter(${$var});
            }
        }
    }

    private function assertTypeConstants() {
        if ($this->getType() !== self::TYPE_SQLITE) {
            throw new \TKMON\Exception\ModelException('Database type is not supported: '. $this->getType());
        }
    }


    /**
     * Creates a sqlite connection
     *
     * - Based on object data
     * - SqLite memory tuning paradigms
     *
     * @return \PDO
     */
    private function createSqLiteConnection()
    {
        $file = $this->getBasePath(). DIRECTORY_SEPARATOR. $this->getName();
        $dsn = 'sqlite:'. $file;
        $pdo = new \PDO($dsn, null, null, self::$defaultOptions);

        // Performance tuning for sqlite
        $pdo->exec('PRAGMA temp_store=MEMORY; PRAGMA journal_mode=MEMORY;');

        return $pdo;
    }

    /**
     * Creates a new connection based on data
     *
     * @param bool $cached Create explicit a new connection
     * @return \PDO
     */
    public function buildConnection($cached = true)
    {
        $this->assertTypeConstants();


        if (!($this->connection instanceof \PDO) || $cached === false) {
            $methodName = self::$connectionMap[$this->getType()];
            $this->connection = $this->$methodName();
        }

        return $this->connection;
    }
}
