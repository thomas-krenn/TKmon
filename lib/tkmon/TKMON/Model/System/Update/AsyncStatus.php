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


namespace TKMON\Model\System\Update;

use SplFileInfo;
use stdClass;
use TKMON\Model\ApplicationModel;

/**
 * Fetch status from async update process
 *
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class AsyncStatus extends ApplicationModel
{
    /**
     * File for update info
     *
     * @var string
     */
    private $infoFile = '/tmp/async_update.info';

    /**
     * File for error info
     *
     * @var string
     */
    private $errorFile = '/tmp/async_update.err';

    /**
     * File for status information
     *
     * @var string
     */
    private $statusFile = '/tmp/async_update.status';

    /**
     * Setter for error file
     *
     * @param string $errorFile
     */
    public function setErrorFile($errorFile)
    {
        $this->errorFile = $errorFile;
    }

    /**
     * Getter for error file
     *
     * @return string
     */
    public function getErrorFile()
    {
        return $this->errorFile;
    }

    /**
     * Setter for info file
     *
     * @param string $infoFile
     */
    public function setInfoFile($infoFile)
    {
        $this->infoFile = $infoFile;
    }

    /**
     * Getter for info file
     *
     * @return string
     */
    public function getInfoFile()
    {
        return $this->infoFile;
    }

    /**
     * Setter for status file
     *
     * @param string $statusFile
     */
    public function setStatusFile($statusFile)
    {
        $this->statusFile = $statusFile;
    }

    /**
     * Getter for status file
     *
     * @return string
     */
    public function getStatusFile()
    {
        return $this->statusFile;
    }

    /**
     * Fetch content from file and truncate data
     *
     * @param   string  $file
     * @param   bool    $truncate   Set true to truncate data
     *
     * @return  string              Content
     */
    private function getContentFromFile($file, $truncate = false)
    {
        if (file_exists($file)) {
            $info = file_get_contents($file);

            if ($truncate === true) {
                file_put_contents($file, '');
            }

            return $info;
        }

        return '';
    }

    /**
     * Get info string from process
     *
     * @param   bool    $truncate
     *
     * @return  string
     */
    public function getInfo($truncate = false)
    {
        return $this->getContentFromFile($this->getInfoFile(), $truncate);
    }

    /**
     * Get error string from process
     *
     * @param   bool    $truncate
     *
     * @return  string
     */
    public function getError($truncate = false)
    {
        return $this->getContentFromFile($this->getErrorFile(), $truncate);
    }

    /**
     * Format date for presentation
     *
     * @param   int $unixEpoch
     * @return  string
     */
    private function formatDate($unixEpoch)
    {
        return date(DATE_ISO8601, $unixEpoch);
    }

    /**
     * Get status information from process
     *
     * @return stdClass
     */
    public function getStatus()
    {
        $content = $this->getContentFromFile($this->getStatusFile(), false);

        if ($content) {
            $data = explode(' ', $content);

            $fileInfo = new SplFileInfo($this->getStatusFile());

            $out                    = new stdClass();
            $out->startTimeSeconds  = (int) substr($data[0], 0, strpos($data[0], '.'));
            $out->startTime         = $this->formatDate($out->startTimeSeconds);
            $out->nowSeconds        = time();
            $out->runTime           = (float) $data[1];
            $out->progress          = (float) $data[2];
            $out->isRunning         = ($data[3] === 'True' ? true : false);
            $out->hasErrors         = ($data[4] === 'True' ? true : false);
            $out->lastUpdate        = $this->formatDate($fileInfo->getMTime());

            return $out;
        }

        return null;
    }
}
