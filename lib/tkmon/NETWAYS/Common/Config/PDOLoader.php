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

namespace NETWAYS\Common\Config;

/**
 * Load config settings from database
 *
 * @package NETWAYS\Common\Config
 * @author Marius Hein <marius.hein@netways.de>
 */
class PDOLoader implements LoadInterface
{

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
     * Flag for statement
     * @var bool
     */
    private $isExecuted = false;

    /**
     * Current key
     * @var string
     */
    private $currentKey;

    /**
     * Current value
     * @var string
     */
    private $currentVal;

    /**
     * Prepared database statement
     * @var \PDOStatement
     */
    private $statement;

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
     * Setter to configure the key column
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
     * Setter for the pdo object
     *
     * @param \PDO $pdo
     */
    public function setPdo($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Getter for PDO
     *
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
     * Prepares the object to deliver data
     *
     * Interface method
     *
     */
    public function prepareLoading()
    {
        if ($this->statement === null) {
            $sql = sprintf(
                'SELECT %s,%s from %s ORDER BY %s;',
                $this->getKeyColumn(),
                $this->getValueColumn(),
                $this->getTable(),
                $this->getKeyColumn()
            );

            $this->statement = $this->getPdo()->prepare($sql);
        }

        if ($this->isExecuted === true) {
            $this->isExecuted = false;
            $this->statement->closeCursor();
        }

        $this->statement->execute();
        $this->isExecuted = true;
    }

    /**
     * Return the current method
     * Interface from Iterator
     * @return mixed|string
     */
    public function current()
    {
        return $this->currentVal;
    }

    /**
     * Return the current key
     * Interface from Iterator
     * @return mixed|string
     */
    public function key()
    {
        return $this->currentKey;
    }

    /**
     * Next record
     * Interface from Iterator
     * Not needed by this object
     */
    public function next()
    {
        // PASS
    }

    /**
     * Test statement for further values
     * This fetches the values
     * @return bool
     */
    public function valid()
    {
        $res = $this->statement->fetch();
        if ($res !== false) {
            $this->currentKey = $res[$this->keyColumn];
            $this->currentVal = $res[$this->valueColumn];
            return true;
        }

        $this->statement->closeCursor();
        $this->isExecuted = false;

        $this->currentKey = null;
        $this->currentVal = null;

        return false;
    }

    /**
     * Erase and rewind.
     * Resend the query
     */
    public function rewind()
    {
        $this->prepareLoading();
    }
}
