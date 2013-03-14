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

namespace TKMON\Model\System;

/**
 * Model to write NTP configuration
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class NtpConfiguration extends \TKMON\Model\ApplicationModel
{

    /**
     * Temp prefix for tmp file object
     */
    const TEMP_PREFIX = 'tkmon-ntp-config';

    /**
     * Config file
     * @var string
     */
    private $configFile = '/etc/ntp.conf';

    /**
     * NTP servers
     * @var array
     */
    private $ntpServers = array();

    /**
     * How many servers allowed
     * @var int
     */
    private $maxServers = 99;

    /**
     * Lines of the config file
     * @var array
     */
    private $lines = array();

    /**
     * Setter for config file
     * @param $configFile
     */
    public function setConfigFile($configFile)
    {
        $this->configFile = $configFile;
    }

    /**
     * Getter for config file
     * @return string
     */
    public function getConfigFile()
    {
        return $this->configFile;
    }

    /**
     * Setter for maxServers
     * @param $maxServers
     */
    public function setMaxServers($maxServers)
    {
        $this->maxServers = $maxServers;
    }

    /**
     * Getter for maxServers
     * @return int
     */
    public function getMaxServers()
    {
        return $this->maxServers;
    }

    /**
     * Add ntp server to stack
     * @param $server
     * @param null $index
     * @throws \TKMON\Exception\ModelException
     */
    public function addNtpServer($server, $index = null)
    {

        if (count($this->ntpServers) >= $this->getMaxServers()) {
            throw new \TKMON\Exception\ModelException('Maximum server items reach');
        }

        if ($index === null) {
            $this->ntpServers[] = $server;
        } else {
            $this->ntpServers[$index] = $server;
        }
    }

    /**
     * Get specific ntp server from stacl
     * @param int $index
     * @return null|string
     */
    public function getNtpServer($index = 0)
    {
        if (isset($this->ntpServers[$index])) {
            return $this->ntpServers[$index];
        }

        return null;
    }

    /**
     * Get the whole server array
     * @return array
     */
    public function getNtpServers()
    {
        return $this->ntpServers;
    }

    /**
     * Purge all servers to write new
     */
    public function purgeServers()
    {
        unset($this->ntpServers);
        $this->ntpServers = array();
    }

    /**
     * Assertion that config file exists
     * @throws \TKMON\Exception\ModelException
     */
    private function assertExistingConfigurationFile()
    {
        if (!file_exists($this->getConfigFile())) {
            throw new \TKMON\Exception\ModelException('NTP config does not exist: '. $this->getConfigFile());
        }
    }

    /**
     * Load data into object
     */
    public function load()
    {
        $this->assertExistingConfigurationFile();

        $fo = new \SplFileObject($this->getConfigFile());
        $m = array();

        while (!$fo->eof()) {
            $line = $fo->fgets();
            if (preg_match('/^\s*server\s+([^$]+)$/', $line, $m)) {
                try {
                    $this->addNtpServer(trim($m[1]));
                } catch (\TKMON\Exception\ModelException $e) {
                    // PASS
                }
            } elseif (preg_match('/###TKMON###/', $line)) {
                continue;
            } else {
                $this->lines[] = $line;
            }
        }
    }

    /**
     * Write data from object into config file
     */
    public function write()
    {
        $this->assertExistingConfigurationFile();

        $fo = new \NETWAYS\IO\RealTempFileObject(self::TEMP_PREFIX, 'w+');

        foreach ($this->lines as $line) {
            $fo->fwrite($line);
        }

        if (count($this->ntpServers)) {
            $fo->fwrite('# ###TKMON### '. str_repeat('-', 66). PHP_EOL);
            $fo->fwrite('# ###TKMON### NTP server added by '. __NAMESPACE__. '\\'.  __CLASS__. PHP_EOL);
            foreach ($this->ntpServers as $server) {
                $fo->fwrite('server '. $server. PHP_EOL);
            }
            $fo->fwrite('# ###TKMON### '. str_repeat('-', 66). PHP_EOL);
        }

        $fo->fflush();

        /** @var $mv \NETWAYS\IO\Process */
        $mv = $this->container['command']->create('mv');
        $mv->addPositionalArgument($fo->getRealPath());
        $mv->addPositionalArgument($this->getConfigFile());
        $mv->execute();

        unset($fo);
    }
}
