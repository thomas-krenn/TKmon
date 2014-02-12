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
    /**
     * Identifier for update-notifier-common if a restart is required
     *
     * @var string
     */
    const REBOOT_REQUIRED_FILE = '/var/run/reboot-required';

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
        $repoData = explode('/', $repository, 2);

        if (count($repoData) !== 2) {
            return null;
        }

        $ubuntuVersion = array_shift($repoData);
        $repository = array_shift($repoData);

        if (strpos($repository, '-security') !== false) {
            $repository = str_replace('-security', '-updates', $repository);
        }

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
            $aptGet->addEnvironment('DEBIAN_FRONTEND', 'noninteractive');

            $aptGet->addPositionalArgument('-qq');

            // Keep changes in files when do updates
            // @see https://www.netways.org/issues/2435
            $aptGet->addNamedArgument('-o', 'Dpkg::Options::=--force-confold');

            // Use dist-upgrade here. This install also new dependencies
            // for other packages
            // @see https://www.netways.org/issues/2312
            $aptGet->addPositionalArgument('dist-upgrade');

            $aptGet->addPositionalArgument('-y');

            $aptGet->ignoreStdErr();

            $aptGet->execute();

            $output = $aptGet->getOutput();

            if ($aptGet->getProcessError()) {
                if ($output) {
                    $output .= chr(13) . '---' . chr(13);
                }

                $output .= $aptGet->getProcessError();
            }

            return $output;
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

        // To fetch also dependencies use dist-upgrade
        // @see https://www.netways.org/issues/2342
        $aptGet->addPositionalArgument('dist-upgrade');

        $aptGet->execute();

        $output     = $aptGet->getOutput();
        $records    = array();

        $lines = explode(PHP_EOL, $output);
        foreach ($lines as $line) {
            if ($line && preg_match('/^(inst|conf|remv)\s+/i', $line)) {
                $parts = explode(' ', trim($line, '[] '));
                $operation = strtolower($parts[0]);

                if ($operation === 'conf') {
                    continue;
                }

                $record                 = new \stdClass();
                $record->operation      = $operation;
                $record->packageName    = $parts[1];
                array_shift($parts);
                array_shift($parts);
                $record->detail         = implode(' ', $parts);
                $records[]              = $record;
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

    /**
     * Test if a system restart is required
     *
     * @return bool
     */
    public function isRestartRequired()
    {
        if (file_exists(self::REBOOT_REQUIRED_FILE) === true) {
            return true;
        }

        return false;
    }
}
