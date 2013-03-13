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

namespace TKMON\Model\System\Configuration;

/**
 * Base tool class for dump classes
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class Base extends \TKMON\Model\ApplicationModel
{
    const FILE_DB_NAME = 'data.sql';
    const FILE_META_NAME = 'MANIFEST.json';
    const TEMP_PREFIX = 'tkmon-dump-';

    /**
     * Structure of sub directories
     * @var string[]
     */
    protected static $exportParts = array(
        'db', 'icinga', 'config', 'system'
    );

    /**
     * List of config files being exported
     * @var string[]
     */
    protected static $systemConfigFiles = array(
        'config-db.php',
        'config.json',
        'nav.json',
        'services-custom.json',
        'services-default-debian.json'
    );

    /**
     * Create a temp directory
     * @return string
     */
    public function createTempDir()
    {
        $tmpDir = $this->container['tmp_dir'];
        $dirName = tempnam($tmpDir, self::TEMP_PREFIX);

        if (file_exists($dirName)) {
            unlink($dirName);
        }

        mkdir($dirName);

        return $dirName;
    }

    /**
     * Creates an object with paths
     *
     * All paths you need to export all data
     *
     * @return \stdClass
     */
    public function createTempStruct()
    {
        $out = new \stdClass();
        $tmpDir = $this->createTempDir();

        $out->base = $tmpDir;

        foreach (self::$exportParts as $part) {
            $dir = $tmpDir. DIRECTORY_SEPARATOR. $part;
            mkdir($dir);
            $out->{$part} = $dir;
        }
        return $out;
    }
}
