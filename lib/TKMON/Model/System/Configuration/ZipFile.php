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
 * Zip file, created from misc stream data
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
/**
 *
 */
class ZipFile extends Base
{
    /**
     * Temp prefix for zip files
     */
    const TEMP_PREFIX = 'tkmon-zip-content';

    /**
     * If the zip content is password protected
     * @var string
     */
    private $password;

    /**
     * Setter for password
     * @param $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Getter for password
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Extract contents from stdin to disk
     * @return string directory extracted to
     */
    public function extractStandardInToDisk()
    {
        $sourceStream = fopen('php://input', 'r');
        $dir =  $this->extractZipStreamToDisk($sourceStream);
        fclose($sourceStream);
        return $dir;
    }

    /**
     * Extract a zip stream to directory
     * @param resource $sourceStream
     * @return string Name of directory extracted to
     */
    public function extractZipStreamToDisk($sourceStream)
    {
        $tempDir = sys_get_temp_dir();
        $targetFile = tempnam($tempDir, self::TEMP_PREFIX);
        $targetStream = fopen($targetFile, 'w');
        stream_copy_to_stream($sourceStream, $targetStream);
        fclose($targetStream);

        $dir = $this->createTempDir();

        /** @var $unzip \NETWAYS\IO\Process */
        $unzip = $this->container['command']->create('unzip');
        $unzip->addNamedArgument('-d', $dir);

        if ($this->getPassword()) {
            $unzip->addNamedArgument('-P', $this->getPassword());
        }

        $unzip->addPositionalArgument($targetFile);
        $unzip->execute();
        unlink($targetFile);

        return $dir;
    }
}
