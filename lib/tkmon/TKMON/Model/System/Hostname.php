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
 * Model to deal with system name
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class Hostname extends \TKMON\Model\ApplicationModel
{
    /**
     * Temp name prefix
     */
    const TEMP_PREFIX = 'hostname-model-';

    /**
     * Ascii charcode for tab
     */
    const ASCII_TAB = 9;

    /**
     * Default location of local hosts file
     */
    const FILE_HOSTS = '/etc/hosts';

    /**
     * Default location of hostname file
     */
    const FILE_HOST_NAME = '/etc/hostname';

    /**
     * Default location of mailname file
     */
    const FILE_MAILNAME = '/etc/mailname';

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
     * Mailname file (e.g. postfix)
     * @var string
     */
    private $mailnameFile = self::FILE_MAILNAME;

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

    /**
     * Array of default hosts should be append to hosts file
     *
     * This is equivalent to ubuntu's default /etc/hosts
     * file after installation.
     *
     * @var array
     */
    private static $defaultHostEntries = array(
        '127.0.0.1' => 'localhost loopback',
        '::1'       => 'ip6-localhost ip6-loopback',
        'fe00::0'   => 'ip6-localnet',
        'ff00::0'   => 'ip6-mcastprefix',
        'ff02::1'   => 'ip6-allnodes',
        'ff02::2'   => 'ip6-allrouters'
    );

    /**
     * Creates a new hostname model
     * @param \Pimple $container
     */
    public function __construct(\Pimple $container)
    {
        parent::__construct($container);
    }

    /**
     * Loads current hostname configuration into the object
     */
    public function load()
    {
        /** @var $command \NETWAYS\IO\Process */
        $command = $this->container['command']->create('hostname');
        $command->addNamedArgument('--long');
        $command->execute();

        $hostname = $command->getOutput();

        $this->setCombined($hostname);
        $this->oldHostname = $this->getHostname();
    }

    /**
     * Setter for domainName
     * @param string $domainName
     */
    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;
    }

    /**
     * Getter for domainName
     * @return string
     */
    public function getDomainName()
    {
        return $this->domainName;
    }

    /**
     * Setter for hostname
     * @param string $hostname
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
    }

    /**
     * Getter for hostname
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
     * Setter for mailname file
     * @param string $mailnameFile
     */
    public function setMailnameFile($mailnameFile)
    {
        $this->mailnameFile = $mailnameFile;
    }

    /**
     * Getter for mailname file
     * @return string
     */
    public function getMailnameFile()
    {
        return $this->mailnameFile;
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

        $hostName = array_shift($data);
        $this->setHostname($hostName);

        $domainName = array_shift($data);
        if ($domainName) {
            $this->setDomainName($domainName);
        } else {
            $this->domainName = null;
        }
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

        $out = $this->hostname;

        if ($this->domainName) {
            $out .= '.'. $this->domainName;
        }

        return $out;
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

        $out .= $this->createDefaultHostsSection();
        $out .= '# EOF';

        return $out;
    }

    /**
     * Create a ubuntu default hosts section
     *
     * # Adding default entries
     * 127.0.0.1    localhost loopback
     * ::1    ip6-localhost ip6-loopback
     * fe00::0    ip6-localnet
     * ff00::0    ip6-mcastprefix
     * ff02::1    ip6-allnodes
     * ff02::2    ip6-allrouters
     *
     * Data is appended from static data on top
     *
     * @see https://www.netways.org/issues/2318
     *
     * @return string
     */
    private function createDefaultHostsSection()
    {
        $out = array(
            '# Add default entries'
        );

        foreach (self::$defaultHostEntries as $address => $name) {
            $out[] = sprintf('%-20s%s', $address, $name);
        }

        return PHP_EOL. implode(PHP_EOL, $out). PHP_EOL;
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
        $hostsFile->chmod(0644);

        $mv->addPositionalArgument($hostsFile->getRealPath());
        $mv->addPositionalArgument($this->getHostsFile());
        $mv->execute();
        $mv->resetPositionalArguments();

        $hostnameFile = new \NETWAYS\IO\RealTempFileObject(self::TEMP_PREFIX, 'w');
        $hostnameFile->fwrite($this->getHostnameContent());
        $hostnameFile->fflush();
        $hostnameFile->chmod(0644);

        $mv->addPositionalArgument($hostnameFile->getRealPath());
        $mv->addPositionalArgument($this->getHostnameFile());
        $mv->execute();
        $mv->resetPositionalArguments();

        $mailnameFile = new \NETWAYS\IO\RealTempFileObject(self::TEMP_PREFIX, 'w');
        $mailnameFile->fwrite($this->getCombined());
        $mailnameFile->fflush();
        $mailnameFile->chmod(0644);

        // Also update /etc/mailname for postfix
        $mv->addPositionalArgument($mailnameFile->getRealPath());
        $mv->addPositionalArgument($this->getMailnameFile());
        $mv->execute();

        /** @var $hostname \NETWAYS\IO\Process **/
        $hostname = $this->container['command']->create('hostname');
        $hostname->addNamedArgument('-F', $this->getHostnameFile());
        $hostname->execute();
    }
}
