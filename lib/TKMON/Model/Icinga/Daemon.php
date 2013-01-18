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

    private $statusFile = '/var/lib/icinga/status.dat';

    public function setStatusFile($statusFile)
    {
        $this->statusFile = $statusFile;
    }

    public function getStatusFile()
    {
        return $this->statusFile;
    }

    public function getStatusTimestamp()
    {
        if (!file_exists($this->getStatusFile())) {
            throw new \TKMON\Exception\ModelException("Statusfile does not exist");
        }

        $fo = new \SplFileObject($this->getStatusFile(), 'r');
        $timestamp = null;
        $m = array();

        foreach ($fo as $line) {
            if (preg_match('/created=(\d+)/', $line, $m)) {
                $timestamp = $m[1];
                break;
            }
        }

        unset($fo);

        return $timestamp;
    }



}
