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

namespace TKMON\Model;

use NETWAYS\IO\Process;

/**
 * This a collection of common actions
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class System extends ApplicationModel
{

    /**
     * Modelled action to execute a system reboot
     */
    public function doReboot()
    {
        /** @var Process $command */
        $command = $this->container['command']->create('reboot');
        $command->execute();
    }

    /**
     * Modelled action to execute a system halt
     */
    public function doHalt()
    {
        /** @var Process $command */
        $command = $this->container['command']->create('halt');
        $command->execute();
    }

    /**
     * Kill dhcp client daemon
     *
     * See #2300 for more information. Dhclient is always
     * changing Ip address if changed to static network
     */
    public function killDhcpClient()
    {
        /** @var Process $command */
        $command = $this->container['command']->create('pkill');
        $command->addPositionalArgument('dhclient');
        $command->execute();
    }

    /**
     * Restart main interface
     */
    public function restartNetworkInterfaces()
    {
        $interface = $this->container['config']['system.interface'];

        /** @var $command Process */
        $command = $this->container['command']->create('systemctl');
        $command->addPositionalArgument('restart');
        $command->addPositionalArgument('networking.service');
        // $command->addPositionalArgument('INTERFACE='. $interface);
        $command->execute();

        /*
         * Testing for Thomas-Krenn issue script and
         * start if available
         */

        $file = '/etc/init/tkmon-issue.conf';
        if (file_exists($file)) {
            $command = $this->container['command']->create('start');
            $command->addPositionalArgument('tkmon-issue');
            $command->execute();
        }
    }

    /**
     * Restart the running ntp daemon
     */
    public function restartNtpDaemon()
    {
        /** @var $command Process */
        $command = $this->container['command']->create('service');
        $command->addPositionalArgument('ntp');
        $command->addPositionalArgument('restart');
        $command->execute();
    }

    /**
     * Restart postfix mail service
     */
    public function restartPostfix()
    {
        /** @var $command Process */
        $command = $this->container['command']->create('service');
        $command->addPositionalArgument('postfix');
        $command->addPositionalArgument('restart');
        $command->execute();
    }

    /**
     * Reloads apache
     *
     * Restart is not possible because of loosing the connection
     */
    public function restartApache()
    {
        /** @var $command Process */
        $command = $this->container['command']->create('service');
        $command->addPositionalArgument('apache2');
        $command->addPositionalArgument('reload');
        $command->execute();
    }

    /**
     * Change owner to apache owner
     *
     * - recursively
     *
     * @param string $dir target directory
     */
    public function chownRecursiveToApache($dir)
    {
        if (strpos($dir, '/vagrant') === 0) {
            return;
        }
        /** @var $chown Process */
        $chown = $this->container['command']->create('chown');
        $chown->addNamedArgument('-R');
        // $chown->addNamedArgument('-f');
        $chown->addPositionalArgument($this->container['config']['system.apache_owner']);
        $chown->addPositionalArgument($dir);
        $chown->execute();
    }
}
