<?php

namespace TKMON\Model\Database;

/**
 * Class to import our default sqlite schema into the database
 * @package TKMON
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
    public function databaseExists() {
        return is_file($this->database);
    }

    /**
     * Imports the default schema into a database
     */
    public function createDefaultDatabase() {
        $pdo = new \PDO('sqlite:'. $this->getDatabase(), null, null, array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        ));

        $pdo->exec(file_get_contents($this->getSchema()));

        unset($pdo);
    }
}
