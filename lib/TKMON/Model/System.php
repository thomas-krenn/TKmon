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

namespace TKMON\Model;

/**
 * This a collection of common actions
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class System extends ApplicationModel
{

    /**
     * Modelled action to excecute a system reboot
     */
    public function doReboot()
    {
        $command = $this->container['command']->create('reboot');
        $command->execute();
    }

    /**
     * Restart main interface
     */
    public function restartNetworkInterfaces()
    {
        $interface = $this->container['config']['system.interface'];

        /** @var $command \NETWAYS\IO\Process */
        $command = $this->container['command']->create('restart');
        $command->addPositionalArgument('network-interface');
        $command->addPositionalArgument('INTERFACE='. $interface);
        $command->execute();
    }

    /**
     * Restart the running ntp daemon
     */
    public function restartNtpDaemon()
    {
        /** @var $command \NETWAYS\IO\Process */
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
        /** @var $command \NETWAYS\IO\Process */
        $command = $this->container['command']->create('service');
        $command->addPositionalArgument('postfix');
        $command->addPositionalArgument('restart');
        $command->execute();
    }
}
