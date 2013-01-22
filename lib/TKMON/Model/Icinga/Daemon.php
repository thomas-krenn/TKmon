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

namespace TKMON\Model\Icinga;

/**
 * Model to get status information from daemon
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class Daemon extends \TKMON\Model\ApplicationModel
{
    /**
     * Location of the status.dat file
     * @var string
     */
    private $statusFile = '/var/lib/icinga/status.dat';

    /**
     * Creation tstamp
     * @var int
     */
    private $infoCreated = null;

    /**
     * Running icinga version
     * @var string
     */
    private $infoVersion = null;

    /**
     * Parts of programmstatus
     * @var array
     */
    private $programStatus = array();

    /**
     * Setter of status file
     * @param string $statusFile
     */
    public function setStatusFile($statusFile)
    {
        $this->statusFile = $statusFile;
    }

    /**
     * Getter of status file
     * @return string
     */
    public function getStatusFile()
    {
        return $this->statusFile;
    }

    /**
     * Resets the object into a neutral state
     */
    public function resetState()
    {
        $this->infoCreated = null;
        $this->infoVersion = null;
        $this->programStatus = array();
    }

    /**
     * Sets the timestamp
     * @param int $infoCreated
     */
    public function setInfoCreated($infoCreated)
    {
        $this->infoCreated = $infoCreated;
    }

    /**
     * Getter for timestamp
     * @return null
     */
    public function getInfoCreated()
    {
        return $this->infoCreated;
    }

    /**
     * Return actuality of the status data in seconds
     * @return int
     */
    public function getCreatedDiffInSeconds()
    {
        return time() - $this->getInfoCreated();
    }

    /**
     * Test if the icinga daemon is running
     *
     * - Testing freshness interval of status file
     * - Testing existence of PID in proc
     *
     * @return bool
     */
    public function daemonIsRunning()
    {
        $freshness = (int)$this->container['config']->get('icinga.freshness', 0);

        if ($this->getCreatedDiffInSeconds() > $freshness) {
            return false;
        }

        $pidFile = DIRECTORY_SEPARATOR
            . 'proc'
            . DIRECTORY_SEPARATOR
            . $this->getProgramStatus('icinga_pid');

        return is_dir($pidFile);
    }

    /**
     * Setter for icinga version
     * @param string $infoVersion
     */
    public function setInfoVersion($infoVersion)
    {
        $this->infoVersion = $infoVersion;
    }

    /**
     * Getter of icinga version
     * @return string
     */
    public function getInfoVersion()
    {
        return $this->infoVersion;
    }

    /**
     * Add status data setting
     * @param string $key setting name
     * @param mixed $val setting value
     */
    public function addProgramStatus($key, $val)
    {
        $this->programStatus[$key] = $val;
    }

    /**
     * Tests of setting existence
     * @param string $key
     * @return bool
     */
    public function existsProgramStatus($key)
    {
        return array_key_exists($key, $this->programStatus);
    }

    /**
     * Getter of program status
     *
     * If $item parameter is omitted, the method
     * will return all settings as an array.
     *
     * Throws an exception if setting is not found
     *
     * @param null|string $item
     * @return array
     * @throws \TKMON\Exception\ModelException
     */
    public function getProgramStatus($item = null)
    {
        if ($item === null) {
            return $this->programStatus;
        } elseif ($this->existsProgramStatus($item)) {
            return $this->programStatus[$item];
        } else {
            throw new \TKMON\Exception\ModelException('Item does not exist: ' . $item);
        }
    }

    /**
     * How to fill the data of the object
     * @param string $context
     * @param string $key
     * @param mixed $val
     */
    private function contextProcessor($context, $key, $val)
    {
        if ($context === "info") {
            $setter = 'setInfo' . ucfirst($key);
            $this->{$setter}($val);
        } elseif ($context === 'programstatus') {
            $this->addProgramStatus($key, $val);
        }
    }

    /**
     * Loads the data from file into the object
     * @throws \TKMON\Exception\ModelException
     */
    public function load()
    {
        if (!file_exists($this->getStatusFile())) {
            throw new \TKMON\Exception\ModelException("status data file does not exist");
        }

        $fo = new \SplFileObject($this->getStatusFile(), 'r');
        $context = null;

        foreach ($fo as $line) {
            $match = array();

            if ($context === null && preg_match('/^(\w+)\s+{/', $line, $match)) {
                $context = $match[1];
            } elseif ($context && preg_match('/\s+(\w+)=([^\n]+)?\n$/', $line, $match)) {
                $this->contextProcessor(
                    $context,
                    $match[1],
                    isset($match[2]) ? $match[2] : null
                );
            } elseif ($context && preg_match('/\s*}\n/', $line)) {
                $context = null;
            }
        }
    }
}
