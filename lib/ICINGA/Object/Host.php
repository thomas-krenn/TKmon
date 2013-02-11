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
 * Host class
 *
 * @package ICINGA
 * @author Marius Hein <marius.hein@netways.de>
 *
 * @property string $hostName
 * @property string $alias
 * @property string $displayName
 * @property string $address
 * @property string $address5
 * @property string $parents
 * @property string $hostgroups
 * @property string $checkCommand
 * @property string $initialState
 * @property string $maxCheckAttempts
 * @property string $checkInterval
 * @property string $retryInterval
 * @property string $activeChecksEnabled
 * @property string $passiveChecksEnabled
 * @property string $checkPeriod
 * @property string $obsessOverHost
 * @property string $checkFreshness
 * @property string $freshnessThreshold
 * @property string $eventHandler
 * @property string $eventHandlerEnabled
 * @property string $lowFlapThreshold
 * @property string $highFlapThreshold
 * @property string $flapDetectionnabled
 * @property string $flapDetectionOptions
 * @property string $failurePredictionEnabled
 * @property string $processPerfData
 * @property string $retainStatusInformation
 * @property string $retainNonstatusInformation
 * @property string $contacts
 * @property string $contactGroups
 * @property string $notificationInterval
 * @property string $firstNotificationNelay
 * @property string $notificationPeriod
 * @property string $notificationOptions
 * @property string $notificationsEnabled
 * @property string $stalkingPptions
 * @property string $notes
 * @property string $notesUrl
 * @property string $actionUrl
 * @property string $iconImage
 * @property string $iconImageAlt
 * @property string $statusmapImage
 * @property string $2dCoord
 *
 */
class Host extends \ICINGA\Base\Object
{
    /**
     * Services for this host
     * @var \NETWAYS\Common\ArrayObject|Service
     */
    private $services;

    /**
     * Create the object and configure the attributes
     */
    public function __construct()
    {
        parent::__construct();

        $this->services = new \NETWAYS\Common\ArrayObject();

        $this->addAttributes(
            array(
                'host_name',
                'alias',
                'display_name',
                'address',
                'address6',
                'parents',
                'hostgroups',
                'check_command',
                'initial_state',
                'max_check_attempts',
                'check_interval',
                'retry_interval',
                'active_checks_enabled',
                'passive_checks_enabled',
                'check_period',
                'obsess_over_host',
                'check_freshness',
                'freshness_threshold',
                'event_handler',
                'event_handler_enabled',
                'low_flap_threshold',
                'high_flap_threshold',
                'flap_detection_enabled',
                'flap_detection_options',
                'failure_prediction_enabled',
                'process_perf_data',
                'retain_status_information',
                'retain_nonstatus_information',
                'contacts',
                'contact_groups',
                'notification_interval',
                'first_notification_delay',
                'notification_period',
                'notification_options',
                'notifications_enabled',
                'stalking_options',
                'notes',
                'notes_url',
                'action_url',
                'icon_image',
                'icon_image_alt',
                'statusmap_image',
                '2d_coords'
            )
        );
    }

    /**
     * Return the hostname
     * @return string
     */
    public function getObjectIdentifier()
    {
        return $this->getHostName();
    }

    /**
     * Tests of the object is valid
     * @throws \ICINGA\Exception\ConfigException
     */
    public function assertObjectIsValid()
    {
        if (!$this->hostName) {
            throw new \ICINGA\Exception\ConfigException('$hostName not set');
        }

        if (!$this->address) {
            throw new \ICINGA\Exception\ConfigException('$address not set');
        }
    }

    /**
     * Add a service to this host
     * @param Service $service
     */
    public function addService(Service $service)
    {
        $service->setHost($this);
        $this->services[$service->serviceDescription] = $service;
    }

    /**
     * Return service by name
     * @param string $name
     * @return Service|null
     */
    public function getService($name)
    {
        if ($this->services->offsetExists($name)) {
            return $this->services->offsetGet($name);
        }

        return null;
    }

    /**
     * Return array of services
     * @return Service[]
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Tests if the service exists
     * @param Service $service
     * @return bool
     */
    public function serviceExists(Service $service)
    {
        return array_key_exists($service->serviceDescription, $this->services);
    }

    /**
     * Remove a service from stack
     * @param Service $service
     */
    public function removeService(Service $service)
    {
        if ($this->serviceExists($service)) {
            unset($this->services[$service->serviceDescription]);
        }
    }

    /**
     * Drop all services from object
     */
    public function purgeService()
    {
        $this->services = array();
    }

    /**
     * Test if the host has services
     * @return bool
     */
    public function hasServices()
    {
        return (count($this->services) > 0)  ? true : false;
    }

    /**
     * Convert this and all sub objects to string
     * @return string
     */
    public function toString()
    {
        $out = parent::toString();

        if ($this->hasServices()) {
            $out .= PHP_EOL. PHP_EOL;
            foreach ($this->services as $service) {
                $out .= (string)$service. PHP_EOL. PHP_EOL;
            }

            // Remove last new line
            $out = substr($out, 0, -1);
        }

        return $out;
    }
}
