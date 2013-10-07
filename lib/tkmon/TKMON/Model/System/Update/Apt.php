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


namespace TKMON\Model\System\Update;

use NETWAYS\IO\Process;
use TKMON\Exception\ModelException;
use TKMON\Model\ApplicationModel;

/**
 * Apt updates and information about updates
 *
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class Apt extends ApplicationModel
{
    const APT_REGEX = '/(inst|conf|remv)\s+([^\s]+)\s(\[([^\]]+)\]\s+)?\(([^\s]+)\s([^\s]+)\s\[([^\]]+)\]\)/i';

    /**
     * Generates href to follow package information
     *
     * @param string $repository
     * @param string $packageName
     *
     * @return string URL to packages.ubuntu.com
     */
    private function createPackageHref($repository, $packageName)
    {
        list($ubuntuVersion, $repository) = explode('/', $repository, 2);
        return 'http://packages.ubuntu.com/'. $repository. '/'. $packageName;
    }

    /**
     * Do a system upgrade
     *
     * @return string
     * @throws ModelException
     */
    public function doUpgrade()
    {
        $pending = $this->getPendingUpdates();

        if (count($pending)) {
            /** @var Process $aptGet */
            $aptGet = $this->container['command']->create('apt-get');
            $aptGet->addEnvironment('DEBIAN_PRIORITY', 'critical');
            $aptGet->addEnvironment('DEBIAN_FRONTEND', 'noninteractive');
            $aptGet->addPositionalArgument('-qq');
            $aptGet->addPositionalArgument('upgrade');
            $aptGet->addPositionalArgument('-y');
            $aptGet->execute();

            return $aptGet->getOutput();
        } else {
            throw new ModelException('No pending updates found');
        }
    }

    /**
     * Fetch updates from repositories
     */
    public function refreshPackages()
    {
        /** @var Process $aptGet */
        $aptGet = $this->container['command']->create('apt-get');
        $aptGet->addEnvironment('DEBCONF_PRIORITY', 'critical');
        $aptGet->addEnvironment('DEBIAN_FRONTEND', 'noninteractive');
        $aptGet->addPositionalArgument('-q');
        $aptGet->addPositionalArgument('update');
        $aptGet->addPositionalArgument('-y');
        $aptGet->execute();

        return $aptGet->getOutput();
    }

    /**
     * Return a list of pending updates
     * @return array List of update records
     */
    public function getPendingUpdates()
    {
        $this->refreshPackages();

        /** @var Process $aptGet */
        $aptGet = $this->container['command']->create('apt-get');
        $aptGet->addEnvironment('DEBIAN_FRONTEND', 'noninteractive');
        $aptGet->addPositionalArgument('--just-print');
        $aptGet->addPositionalArgument('upgrade');
        $aptGet->execute();

        $output = $aptGet->getOutput();
        $match = array();
        $records = array();

        if (preg_match_all(self::APT_REGEX, $output, $match, PREG_SET_ORDER)) {
            foreach ($match as $index => $parts) {

                $operation = strtolower($parts[1]);

                if ($operation === 'conf') {
                    continue;
                }

                $record = new \stdClass();
                $record->operation = $operation;
                $record->packageName = $parts[2];
                $record->broke = ($parts[4]) ? $parts[4] : null;
                $record->version = $parts[5];
                $record->repository = $parts[6];
                $record->architecture = $parts[7];
                $record->href = $this->createPackageHref($record->repository, $record->packageName);
                $records[] = $record;
            }
        }

        return $records;
    }

    /**
     * Run apt check to detect broken packages
     *
     * @throws ModelException
     * @return string
     */
    public function testPackages()
    {
        /** @var Process $aptGet */
        $aptGet = $this->container['command']->create('apt-get');
        $aptGet->addEnvironment('DEBIAN_FRONTEND', 'noninteractive');
        $aptGet->addPositionalArgument('check');
        $aptGet->ignoreStdErr(true);
        $aptGet->ignoreProcessReturn(true);
        $aptGet->execute();

        if ($aptGet->getExitStatus() !== 0) {
            $output = $aptGet->getOutput();
            $output .= PHP_EOL. $aptGet->getProcessError();

            throw new ModelException($output);
        }

        return $aptGet->getOutput();
    }

    /**
     * Run apt cache stats
     *
     * @return string
     */
    public function getStats()
    {
        /** @var Process $aptGet */
        $aptGet = $this->container['command']->create('apt-cache');
        $aptGet->addEnvironment('DEBIAN_FRONTEND', 'noninteractive');
        $aptGet->addPositionalArgument('stats');
        $aptGet->execute();
        return $aptGet->getOutput();
    }
}
