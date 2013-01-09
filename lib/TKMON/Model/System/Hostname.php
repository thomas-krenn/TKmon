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
 * This a collection of common actions
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class Hostname extends \TKMON\Model\ApplicationModel
{

    const TEMP_PREFIX = 'hostname-model-';
    const ASCII_TAB = 9;

    const FILE_HOSTS = '/etc/hosts';
    const FILE_HOST_NAME = '/etc/hostname';

    /**
     * Filename of hostname file
     * @var string
     */
    private $hostnameFile = self::FILE_HOST_NAME;

    /**
     * Filename of hosts file (resolver)
     * @var string
     */
    private $hostsFile = self::FILE_HOSTS;

    /**
     * hostname / device name
     * @var string
     */
    private $hostname;

    /**
     * Fully qualified domain name
     * @var string
     */
    private $domainName;

    /**
     * Hostname from load process
     * @var string
     */
    private $oldHostname;

    public function __construct(\Pimple $container) {
        parent::__construct($container);
    }

    /**
     * Loads current hostname configuration into the object
     */
    public function load() {
        /** @var $command \NETWAYS\IO\Process */
        $command = $this->container['command']->create('hostname');
        $command->addNamedArgument('--long');
        $command->execute();

        $hostname = $command->getOutput();

        $this->setCombined($hostname);
        $this->oldHostname = $this->getHostname();
    }

    /**
     * @param string $domainName
     */
    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;
    }

    /**
     * @return string
     */
    public function getDomainName()
    {
        return $this->domainName;
    }

    /**
     * @param string $hostname
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
    }

    /**
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * Setter for hosts file
     * @param string $hostsFile
     */
    public function setHostsFile($hostsFile)
    {
        $this->hostsFile = $hostsFile;
    }

    /**
     * Getter for hosts file
     * @return string
     */
    public function getHostsFile()
    {
        return $this->hostsFile;
    }

    /**
     * Setter for hostname file
     * @param string $hostnameFile
     */
    public function setHostnameFile($hostnameFile)
    {
        $this->hostnameFile = $hostnameFile;
    }

    /**
     * Getter for hostnamefile
     * @return string
     */
    public function getHostnameFile()
    {
        return $this->hostnameFile;
    }

    /**
     * Sets the host and domain together
     * @param $fullHostname
     * @throws \TKMON\Exception\ModelException
     * @return void
     */
    public function setCombined($fullHostname)
    {
        $data = explode('.', $fullHostname, 2);

        if (!is_array($data) || count($data) !== 2) {
            throw new \TKMON\Exception\ModelException('Not a valid hostname: '. $fullHostname);
        }

        list($host, $domain) = $data;

        $this->setHostname($host);
        $this->setDomainName($domain);
    }

    /**
     * Getter for the "full" hostname
     * @return string
     * @throws \TKMON\Exception\ModelException
     */
    public function getCombined()
    {

        if (!$this->hostname) {
            throw new \TKMON\Exception\ModelException('hostname not set');
        }

        if (!$this->domainName) {
            throw new \TKMON\Exception\ModelException('domainName not set');
        }

        return $this->hostname. '.'. $this->domainName;
    }

    /**
     * Template method to create content of "hosts" file
     * @return string
     */
    private function getHostsContent()
    {
        $tab = chr(self::ASCII_TAB);
        $out = '# !!! Autocreated by '
        . __NAMESPACE__
        . '\\'
        . __CLASS__
        . PHP_EOL
        . '# Created on '. date('c')
        . PHP_EOL
        . '127.0.0.1'
        . $tab. $this->getCombined()
        . $tab. $this->getHostname()
        . PHP_EOL;

        if ($this->oldHostname) {
            $out .= '127.0.0.1'
            . $tab. $this->oldHostname
            . PHP_EOL;
        }

        $out .= '# EOF';

        return $out;
    }

    /**
     * Template method to create hostname file
     * @return string
     */
    public function getHostnameContent()
    {
        return $this->getHostname();
    }

    /**
     * Write changes to disc
     */
    public function write()
    {
        /** @var $mv \NETWAYS\IO\Process **/
        $mv = $this->container['command']->create('mv');

        $hostsFile = new \NETWAYS\IO\RealTempFileObject(self::TEMP_PREFIX, 'w');
        $hostsFile->fwrite($this->getHostsContent());
        $hostsFile->fflush();

        $mv->addPositionalArgument($hostsFile->getRealPath());
        $mv->addPositionalArgument($this->getHostsFile());
        $mv->execute();
        $mv->resetPositionalArguments();

        $hostnameFile = new \NETWAYS\IO\RealTempFileObject(self::TEMP_PREFIX, 'w');
        $hostnameFile->fwrite($this->getHostnameContent());
        $hostnameFile->fflush();

        $mv->addPositionalArgument($hostnameFile->getRealPath());
        $mv->addPositionalArgument($this->getHostnameFile());
        $mv->execute();

        /** @var $hostname \NETWAYS\IO\Process **/
        $hostname = $this->container['command']->create('hostname');
        $hostname->addNamedArgument('-F', $this->getHostnameFile());
        $hostname->execute();
    }
}
