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
 * Manifest for configuration data
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class Manifest extends Base
{
    const MANIFEST_VERSION = '1.0.0';

    private $manifestVersion = self::MANIFEST_VERSION;

    private $softwareVersion;

    private $password = false;

    private $baseDir;

    private $sha1Hash;

    private $subObjects = array();

    private $fileList = array();

    public function createFromDirectory($targetDir) {

        $this->baseDir = $targetDir;

        foreach (self::$exportParts as $part) {
            $dir = $targetDir. DIRECTORY_SEPARATOR. $part;
            if (file_exists($dir)) {
                $this->subObjects[] = $part;
            }
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $targetDir,
                \RecursiveDirectoryIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        /** @var $fileInfo \SplFileInfo */
        $fileInfo = null;

        foreach ($iterator as $fileInfo) {
            $file = str_replace($targetDir, '', $fileInfo->getRealPath());
            $this->fileList[] = $file;
        }
    }

    public function writeToFile($fileName)
    {
        file_put_contents($fileName, $this->toString());
    }

    public function setSoftwareVersion($softwareVersion)
    {
        $this->softwareVersion = $softwareVersion;
    }

    public function getSoftwareVersion()
    {
        return $this->softwareVersion;
    }

    public function hasPassword($flag=true)
    {
        $this->password = $flag;
    }

    public function toString()
    {
        $object = new \stdClass();
        $object->manifest = $this->manifestVersion;
        $object->version = $this->getSoftwareVersion();
        $object->created = "NOT SET";
        $object->baseDir = $this->baseDir;
        $object->subObjects = $this->subObjects;
        $object->list = $this->fileList;
        $object->password = $this->password;
        $object->sha1 = sha1('DING');

        return json_encode($object);
    }
}
