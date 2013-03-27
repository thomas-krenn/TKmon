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
    /**
     * Database type sqlite
     * @var string
     */
    const TYPE_SQLITE = 'sqlite';

    /**
     * Database type sqlite3
     * Which is the same as sqlite
     * @var string
     */
    const TYPE_SQLITE3 = 'sqlite3';

    /**
     * Map which setter configures a parameter
     * @var string[]
     */
    private static $setterMap = array(
        'dbuser'    => 'setUser',
        'dbpass'    => 'setPassword',
        'basepath'  => 'setBasePath',
        'dbname'    => 'setName',
        'dbserver'  => 'setServer',
        'dbport'    => 'setPort',
        'dbtype'    => 'setType'
    );

    /**
     * Returns reflection
     *
     * Between database types and connection creators
     *
     * @var callback[]
     */
    private static $connectionMap = array(
        self::TYPE_SQLITE   => 'createSqLiteConnection',
        self::TYPE_SQLITE3  => 'createSqLiteConnection'
    );

    /**
     * Set of default options applies to every connection
     * @var int[]
     */
    private static $defaultOptions = array(
        \PDO::ATTR_PERSISTENT           => true,
        \PDO::ATTR_ERRMODE              => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_CASE                 => \PDO::CASE_LOWER,
        \PDO::ATTR_DEFAULT_FETCH_MODE   => \PDO::FETCH_ASSOC
    );

    /**
     * Database user
     * @var string
     */
    private $user;

    /**
     * Database password
     * @var string
     */
    private $password;

    /**
     * Database path
     * @var string
     */
    private $basePath;

    /**
     * Database name
     * @var string
     */
    private $name;

    /**
     * Database server
     * @var string
     */
    private $server;

    /**
     * Database port
     * @var int
     */
    private $port;

    /**
     * Database type
     * @var string
     */
    private $type;

    /**
     * Real connection object
     * @var \PDO
     */
    private $connection;

    /**
     * Setter for basePath
     * @param string $basePath
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Getter for basePath
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Setter for database name
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
     * Setter for password
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Getter for password
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Setter for port
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * Getter for port
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Setter for server
     * @param string $server
     */
    public function setServer($server)
    {
        $this->server = $server;
    }

    /**
     * Getter for server
     * @return string
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Setter for type
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Getter for type
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Setter for user
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Getter for user
     * @return string
     */
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

        foreach (self::$setterMap as $var => $setter) {
            if (isset(${$var})) {
                $this->$setter(${$var});
            }
        }
    }

    /**
     * Assert that we use supported database type
     * @throws \TKMON\Exception\ModelException
     */
    private function assertTypeConstants()
    {
        if (array_key_exists($this->getType(), self::$connectionMap) === false) {
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
