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

namespace ICINGA\Object;

/**
/**
 * Object class for contacts
 *
 * @package ICINGA
 * @author Marius Hein <marius.hein@netways.de>
 *
 * @property string $contactName
 * @property string $alias
 * @property string $contactgroups
 * @property string $hostNotificationsEnabled
 * @property string $serviceNotificationsEnabled
 * @property string $hostNotificationPeriod
 * @property string $serviceNotificationPeriod
 * @property string $hostNotificationOptions
 * @property string $serviceNotificationOptions
 * @property string $hostNotificationCommands
 * @property string $serviceNotificationCommands
 * @property string $email
 * @property string $pager
 * @property string $addressx
 * @property string $canSubmitCommands
 * @property string $retainStatusInformation
 * @property string $retainNonstatusInformation
 */
class Contact extends \ICINGA\Base\Object
{

    /**
     * Create and configure object
     */
    public function __construct()
    {
        $this->addAttributes(
            array(
                'contact_name',
                'alias',
                'contactgroups',
                'host_notifications_enabled',
                'service_notifications_enabled',
                'host_notification_period',
                'service_notification_period',
                'host_notification_options',
                'service_notification_options',
                'host_notification_commands',
                'service_notification_commands',
                'email',
                'pager',
                'addressx',
                'can_submit_commands',
                'retain_status_information',
                'retain_nonstatus_information',
            )
        );

        parent::__construct();
    }

    /**
     * Create a unique identifier
     *
     * If you using a tuple of objects
     *
     * @return string
     */
    public function getObjectIdentifier()
    {
        return $this->contactName;
    }

    /**
     * Creates an object identified from alias
     * @throws \ICINGA\Exception\ConfigException
     */
    public function createObjectIdentifier()
    {
        if ($this->alias) {
            $this->contactName = parent::normalizeIdentifierName($this->alias);
            return;
        }

        throw new \ICINGA\Exception\ConfigException('Alias is not set');
    }


    /**
     * Test the object before writing
     *
     * @throws \ICINGA\Exception\ConfigException
     * @return void
     */
    public function assertObjectIsValid()
    {
        if (!$this->contactName) {
            throw new \ICINGA\Exception\ConfigException('$contactName not set');
        }

        if (!$this->alias) {
            throw new \ICINGA\Exception\ConfigException('$alias not set');
        }

        if (!$this->email) {
            throw new \ICINGA\Exception\ConfigException('$email not set');
        }
    }
}
