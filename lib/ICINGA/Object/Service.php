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
 * Object class
 *
 * @package ICINGA
 * @author Marius Hein <marius.hein@netways.de>
 *
 * @property string hostName
 * @property string hostgroupName
 * @property string serviceDescription
 * @property string displayName
 * @property string servicegroups
 * @property string isVolatile
 * @property string checkCommand
 * @property string initialState
 * @property string maxCheckAttempts
 * @property string checkInterval
 * @property string retryInterval
 * @property string activeChecksEnabled
 * @property string passiveChecksEnabled
 * @property string checkPeriod
 * @property string obsessOverService
 * @property string checkFreshness
 * @property string freshnessThreshold
 * @property string eventHandler
 * @property string eventHandlerEnabled
 * @property string lowFlapThreshold
 * @property string highFlapThreshold
 * @property string flapDetectionEnabled
 * @property string flapDetectionOptions
 * @property string failurePredictionEnabled
 * @property string processPerfData
 * @property string retainStatusInformation
 * @property string retainNonstatusInformation
 * @property string notificationInterval
 * @property string firstNotificationDelay
 * @property string notificationPeriod
 * @property string notificationOptions
 * @property string notificationsEnabled
 * @property string contacts
 * @property string contactGroups
 * @property string stalkingOptions
 * @property string notes
 * @property string notesUrl
 * @property string actionUrl
 * @property string iconImage
 * @property string iconImageAlt
 *
 */
class Service extends \ICINGA\Base\Object
{

    /**
     * Host
     * @var Host
     */
    private $host;

    /**
     * Creates and configures the new object
     */
    public function __construct()
    {
        $this->addAttributes(
            array(
                'host_name',
                'hostgroup_name',
                'service_description',
                'display_name',
                'servicegroups',
                'is_volatile',
                'check_command',
                'initial_state',
                'max_check_attempts',
                'check_interval',
                'retry_interval',
                'active_checks_enabled',
                'passive_checks_enabled',
                'check_period',
                'obsess_over_service',
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
                'notification_interval',
                'first_notification_delay',
                'notification_period',
                'notification_options',
                'notifications_enabled',
                'contacts',
                'contact_groups',
                'stalking_options',
                'notes',
                'notes_url',
                'action_url',
                'icon_image',
                'icon_image_alt'
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
        return $this->hostName. '_'. $this->serviceDescription;
    }


    /**
     * Test the object before write
     * @return void
     * @throws \ICINGA\Exception\ConfigException
     */
    public function assertObjectIsValid()
    {
        if (!$this->serviceDescription) {
            throw new \ICINGA\Exception\ConfigException('$serviceDescription not set');
        }

        if (!$this->hostName) {
            throw new \ICINGA\Exception\ConfigException('$hostName not set');
        }
    }

    /**
     * Setter for the host
     * @param \ICINGA\Object\Host $host
     */
    public function setHost(Host $host)
    {
        $this->hostName = $host->hostName;
        $this->host = $host;
    }

    /**
     * Getter for the host
     * @return \ICINGA\Object\Host
     */
    public function getHost()
    {
        return $this->host;
    }
}
