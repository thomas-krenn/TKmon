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

namespace TKMON\Model\System\Configuration;

use TKMON\Model\Database\DebConfBuilder;
use Zend\Filter\Dir;

/**
 * Import system configuration
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class Importer extends Base
{

    /**
     * Import configuration from disk
     *
     * This is used after extracting the zip file
     *
     * @param string $dir
     * @param bool $hasPasswordFlag
     */
    public function fromDirectory($dir, $hasPasswordFlag = false)
    {

        // Okay, if something goes wrong, an exception is throws
        $manifest = $this->getManifest($dir, $hasPasswordFlag);

        $paths = $this->createPathsStruct($dir, $manifest);

        if ($paths->db) {
            $this->importDatabase($paths->db);
        }

        if ($paths->icinga) {
            $this->importIcingaConfig($paths->icinga);
        }

        if ($paths->config) {
            $this->importSoftwareConfig($paths->config);
        }

        /**
         * @todo System is missing here
         */
    }

    /**
     * Test manifest
     *
     * - Creates manifest from dir
     * - Tests equality from manifest file
     * - Return object from file
     *
     * @param $dir
     * @param bool $hasPasswordFlag
     * @return Manifest
     * @throws \TKMON\Exception\ModelException
     */
    private function getManifest($dir, $hasPasswordFlag = false)
    {
        $fileName = $dir. DIRECTORY_SEPARATOR. self::FILE_META_NAME;

        if (!file_exists($fileName)) {
            throw new \TKMON\Exception\ModelException('Manifest does not exist!');
        }

        $manifest1 = new Manifest($this->container);
        $manifest1->setSoftAssert($this->softAssert);
        $manifest1->createFromDirectory($dir);
        $manifest1->setSoftwareVersion($this->container['config']['app.version.full']);
        $manifest1->setPassword($hasPasswordFlag);

        $manifest2 = new Manifest($this->container);
        $manifest2->setSoftAssert($this->softAssert);
        $manifest2->fromJsonFile($fileName);

        $manifest2->assertEquality($manifest1);

        return $manifest2;
    }

    /**
     * Create a struct of paths
     *
     * Based on manifest and real base path
     *
     * @param string $basePath
     * @param Manifest $manifest
     * @return \stdClass
     */
    private function createPathsStruct($basePath, Manifest $manifest)
    {
        $parts = $manifest->getSubObjects();
        $out = new \stdClass();

        foreach ($parts as $sub) {
            $new = $basePath. DIRECTORY_SEPARATOR. $sub;
            if (is_dir($new)) {
                $out->{$sub} = $new;
            }
        }

        return $out;
    }

    /**
     * Import database from directory
     * @param string $dir
     * @throws \TKMON\Exception\ModelException
     */
    private function importDatabase($dir)
    {
        $dumpFile = $dir. DIRECTORY_SEPARATOR. self::FILE_DB_NAME;

        if (!file_exists($dumpFile)) {
            throw new \TKMON\Exception\ModelException(
                'Import DB failed: Source file could not be found ('. $dumpFile. ')'
            );
        }

        /** @var DebConfBuilder $dbBuilder */
        $dbBuilder = $this->container['dbbuilder'];
        $dbFile = $dbBuilder->getBasePath(). DIRECTORY_SEPARATOR. $dbBuilder->getName();

        /*
         * Unique name because of existing tkmon files
         */
        $tmpName = $dbBuilder->getBasePath()
            . DIRECTORY_SEPARATOR
            . sprintf(
                'tkmon-database-backup-%s-%s.db',
                uniqid(),
                date('YmdGis')
            );

        /** @var $mv \NETWAYS\IO\Process */
        $mv = $this->container['command']->create('mv');
        $mv->addPositionalArgument($dbFile);
        $mv->addPositionalArgument($tmpName); // Backup
        $mv->execute();

        $this->container['logger']->warn('Backup sqlite database to: '. $tmpName);

        /** @var $sqlite \NETWAYS\IO\Process */
        $sqlite = $this->container['command']->create('sqlite3');
        $sqlite->addPositionalArgument($dbFile);
        $sqlite->setInput(file_get_contents($dumpFile));
        $sqlite->execute();
    }

    /**
     * Import icinga configuration from directory
     * @param string $dir
     * @throws \TKMON\Exception\ModelException
     */
    private function importIcingaConfig($dir)
    {
        $baseDir = $this->container['config']['icinga.dir.base'];

        // This is the origin target name (can be a symlink)
        // and is meant to be the source of the backup name
        // see #2140
        $dirName = basename($baseDir);

        // This the target name, because of this can be a symlink
        // we need the realpath here
        // see #2140
        $baseDir = realpath($baseDir);

        $sourceDir = $dir. DIRECTORY_SEPARATOR. $dirName;

        if (!is_dir($sourceDir)) {
            throw new \TKMON\Exception\ModelException('Icinga source config dir not found: '. $sourceDir);
        }

        $this->container['logger']->warn(
            sprintf(
                'Import config, from %s to %s',
                $sourceDir,
                $baseDir
            )
        );

        /** @var $rm \NETWAYS\IO\Process */
        $rm = $this->container['command']->create('rm');
        $rm->addNamedArgument('-rf');
        $rm->addPositionalArgument($baseDir);
        $rm->execute();

        /** @var $cp \NETWAYS\IO\Process */
        $cp = $this->container['command']->create('cp');
        $cp->addNamedArgument('-rf');
        $cp->addPositionalArgument($sourceDir);
        $cp->addPositionalArgument($baseDir);
        $cp->execute();

        $system = new \TKMON\Model\System($this->container);
        $system->chownRecursiveToApache($baseDir);
    }

    /**
     * Import system (TKMON) configuration
     * @param string $targetDir
     */
    private function importSoftwareConfig($targetDir)
    {
        $etcDir = $this->container['config']['core.etc_dir'];

        /** @var $copy \NETWAYS\IO\Process */
        $copy = $this->container['command']->create('cp');
        $copy->addNamedArgument('-rf');
        $copy->addPositionalArgument($targetDir. DIRECTORY_SEPARATOR. 'tkmon'. DIRECTORY_SEPARATOR);
        $copy->addPositionalArgument($etcDir. DIRECTORY_SEPARATOR. '..'. DIRECTORY_SEPARATOR);
        $copy->execute();

        $system = new \TKMON\Model\System($this->container);
        $system->chownRecursiveToApache($etcDir);
    }
}
