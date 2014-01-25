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

namespace TKMON\Model\Database;

/**
 * Class to import our default sqlite schema into the database
 * @package TKMON/Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class Importer
{
    /**
     * Path of the database
     * @var string
     */
    private $database;

    /**
     * Path to schema file
     * @var string
     */
    private $schema;

    /**
     * Setter for database (file)
     * @param string $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * Getter for database file
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Sets the schema file
     * @param string $schema
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;
    }

    /**
     * Returns the schema file
     * @return string
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Tests if the database file exists
     * @return bool
     */
    public function databaseExists()
    {
        return is_file($this->database);
    }

    /**
     * Imports the default schema into a database
     */
    public function createDefaultDatabase()
    {
        $pdo = new \PDO(
            'sqlite:' . $this->getDatabase(),
            null,
            null,
            array(
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            )
        );

        $pdo->exec(file_get_contents($this->getSchema()));

        unset($pdo);
    }
}
