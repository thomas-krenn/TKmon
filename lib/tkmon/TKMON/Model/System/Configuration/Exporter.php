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

namespace TKMON\Model\System\Configuration;

/**
 * Download and apply system configuration
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class Exporter extends Base
{
    /**
     * Filename
     * @var string
     */
    private $file;

    /**
     * Password to encrypt
     * @var string
     */
    private $password;

    /**
     * Directory export structure is created (if any)
     * @var string
     */
    private $tmpDir;

    /**
     * Setter for file
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * Getter for file
     * @return string
     */
    public function getFile()
    {
        return $this->file;
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
     * Clean up fs if something was created
     */
    public function cleanUp()
    {

        if (!$this->getFile() && !$this->tmpDir) {
            return;
        }

        /** @var $rm \NETWAYS\IO\Process */
        $rm = $this->container['command']->create('rm');
        $rm->addNamedArgument('-rf');

        if ($this->getFile()) {
            $rm->addPositionalArgument($this->getFile());
        }

        if ($this->tmpDir) {
            $rm->addPositionalArgument($this->tmpDir);
        }

        $rm->execute();
    }

    /**
     * Export all to file
     */
    public function toFile()
    {
        $paths = $this->createTempStruct();

        $this->tmpDir = $paths->base;

        $this->exportDatabase($paths->db);
        $this->exportIcingaConfig($paths->icinga);
        $this->exportSoftwareConfig($paths->config);

        /**
         * @todo System is missing here
         */

        $this->writeManifest($paths->base);

        /** @var $zip \NETWAYS\IO\Process */
        $zip = $this->container['command']->create('zip');

        $zip->setWorkDirectory($paths->base);

        if ($this->getPassword()) {
            $zip->addNamedArgument('--password', $this->getPassword());
        }

        $zip->addNamedArgument('-r', $this->getFile());
        $zip->addPositionalArgument('.');

        $zip->execute();
    }

    /**
     * Write manifest for directory
     * @param string $targetDir
     */
    private function writeManifest($targetDir)
    {
        $manifest = new Manifest($this->container);
        $manifest->setSoftwareVersion($this->container['config']['app.version.full']);
        $manifest->createFromDirectory($targetDir);

        if ($this->getPassword()) {
            $manifest->hasPassword(true);
        }

        $manifest->writeToFile($targetDir. DIRECTORY_SEPARATOR. self::FILE_META_NAME);
    }

    /**
     * Export database dump
     * @param string $targetDir
     */
    private function exportDatabase($targetDir)
    {
        /** @var $dbBuilder \TKMON\Model\Database\DebConfBuilder */
        $dbBuilder = $this->container['dbbuilder'];
        $dbFile = $dbBuilder->getBasePath(). DIRECTORY_SEPARATOR. $dbBuilder->getName();

        /** @var $sqlite \NETWAYS\IO\Process */
        $sqlite = $this->container['command']->create('sqlite3');
        $sqlite->addPositionalArgument($dbFile);
        $sqlite->addPositionalArgument('.dump');

        $sqlite->execute();

        $targetFile = $targetDir. DIRECTORY_SEPARATOR. self::FILE_DB_NAME;
        file_put_contents($targetFile, $sqlite->getOutput());
    }

    /**
     * Export icinga configuration
     * @param string $targetDir
     */
    private function exportIcingaConfig($targetDir)
    {
        $exportDir = $this->container['config']['icinga.dir.base'];
        /** @var $copy \NETWAYS\IO\Process */
        $copy = $this->container['command']->create('cp');
        $copy->addNamedArgument('-rf');
        $copy->addNamedArgument('-L');
        $copy->addPositionalArgument($exportDir. DIRECTORY_SEPARATOR);
        $copy->addPositionalArgument($targetDir);
        $copy->execute();
    }

    /**
     * Export tkmon config
     * @param string $targetDir
     */
    private function exportSoftwareConfig($targetDir)
    {
        $sourceDir = $this->container['etc_dir'];

        /** @var $copy \NETWAYS\IO\Process */
        $copy = $this->container['command']->create('cp');
        $copy->addNamedArgument('-rf');
        $copy->addPositionalArgument($sourceDir. DIRECTORY_SEPARATOR);
        $copy->addPositionalArgument($targetDir);
        $copy->execute();
    }
}
