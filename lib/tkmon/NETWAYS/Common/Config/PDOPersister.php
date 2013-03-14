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

namespace NETWAYS\Common\Config;

/**
 * Writes config settings to database
 *
 * @package NETWAYS\Common\Config
 * @author Marius Hein <marius.hein@netways.de>
 */
class PDOPersister implements PersisterInterface
{

    /**
     * SQL tag for value
     */
    const TAG_VALUE = ':value';

    /**
     * SQL tag for key
     */
    const TAG_KEY = ':key';

    /**
     * Table name
     * @var string
     */
    private $table;

    /**
     * Key column name
     * @var string
     */
    private $keyColumn;

    /**
     * Value column name
     * @var string
     */
    private $valueColumn;

    /**
     * Database connection
     * @var \PDO
     */
    private $pdo;

    /**
     * Prepared delete statement
     * @var \PDOStatement
     */
    private $deleteStatement;

    /**
     * Prepared create/update statement
     * @var \PDOStatement
     */
    private $createStatement;

    /**
     * Create a new object
     *
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->setPdo($pdo);
    }

    /**
     * Getter for create statement
     * Do lazy initializing
     * @return \PDOStatement
     */
    private function getCreate()
    {
        if ($this->createStatement === null) {
            $this->createStatement = $this->pdo->prepare(
                sprintf(
                    'INSERT OR REPLACE INTO %s VALUES(NULL, %s, %s);',
                    $this->getTable(),
                    self::TAG_KEY,
                    self::TAG_VALUE
                )
            );
        }

        return $this->createStatement;
    }

    /**
     * Getter for delete statement
     * Do lazy initializing
     * @return \PDOStatement
     */
    private function getDelete()
    {
        if ($this->deleteStatement === null) {
            $this->deleteStatement = $this->pdo->prepare(
                sprintf(
                    'DELETE FROM %s WHERE %s=%s',
                    $this->getTable(),
                    $this->getKeyColumn(),
                    self::TAG_KEY
                )
            );
        }

        return $this->deleteStatement;
    }

    /**
     * Setter for key column
     * @param string $keyColumn
     */
    public function setKeyColumn($keyColumn)
    {
        $this->keyColumn = $keyColumn;
    }

    /**
     * Getter for key column
     * @return string
     */
    public function getKeyColumn()
    {
        return $this->keyColumn;
    }

    /**
     * Setter for pdo
     * @param \PDO $pdo
     */
    public function setPdo($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Getter for pdo
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Setter for table name
     * @param string $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * Getter for table name
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Setter for value column
     * @param string $valueColumn
     */
    public function setValueColumn($valueColumn)
    {
        $this->valueColumn = $valueColumn;
    }

    /**
     * Getter for value column
     * @return string
     */
    public function getValueColumn()
    {
        return $this->valueColumn;
    }

    /**
     * Persists a single value in database
     * @param string $key
     * @param $value
     * @return mixed|void
     */
    public function persist($key, $value)
    {
        $statement = $this->getCreate();
        $statement->bindValue(self::TAG_KEY, $key, \PDO::PARAM_STR);
        $statement->bindValue(self::TAG_VALUE, $value, \PDO::PARAM_STR);
        $statement->execute();

    }

    /**
     * Purge all contents from database
     */
    public function purge()
    {
        $this->pdo->exec('DELETE FROM ' . $this->getTable());
    }

    /**
     * Drop a single item
     * @param $key
     * @return mixed|void
     */
    public function drop($key)
    {
        $statement = $this->getDelete();
        $statement->bindValue(self::TAG_KEY, $key);
        $statement->execute();
    }
}
