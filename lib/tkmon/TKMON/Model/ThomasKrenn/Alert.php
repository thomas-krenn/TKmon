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

namespace TKMON\Model\ThomasKrenn;

use NETWAYS\IO\Process;
use TKMON\Exception\ModelException;
use TKMON\Model\ApplicationModel;

/**
 * Abstract model for working with our DI container
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class Alert extends ApplicationModel
{

    /**
     * Type enum for testing
     * @var string
     */
    const TYPE_TEST = 'test';

    /**
     * Type enum for heartbeat
     * @var string
     */
    const TYPE_HEARTBEAT = 'heartbeat';

    /**
     * Type enum for alerting services
     * @var string
     */
    const TYPE_SERVICE = 'service';

    /**
     * Name of preconfigures command
     * @var string
     */
    const COMMAND_NAME = 'tkalert.sh';

    /**
     * Alert Type
     *  - heartbeat
     *  - service
     *  - test
     *
     * @var string
     */
    private $type;

    /**
     * AuthKey to validate ThomasKrenn service
     * @var string
     */
    private $authKey;

    /**
     * Name of responsible person
     * @var string
     */
    private $contactName;

    /**
     * Email address of responsible person
     * @var string
     */
    private $contactMail;

    /**
     * Process object to call
     * @var Process
     */
    private $processObject;

    /**
     * Flag to indicate that object is prepared
     * @var bool
     */
    private $prepared = false;

    public function __construct(\Pimple $container)
    {
        parent::__construct($container);
        $this->processObject = $container['command']->create(self::COMMAND_NAME);
    }

    /**
     * Setter for authKey
     * @param string $authKey
     */
    public function setAuthKey($authKey)
    {
        $this->authKey = $authKey;
    }

    /**
     * Getter for authKey
     * @return string
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * Setter for contactMail
     * @param string $contactMail
     */
    public function setContactMail($contactMail)
    {
        $this->contactMail = $contactMail;
    }

    /**
     * Getter for contactMail
     * @return string
     */
    public function getContactMail()
    {
        return $this->contactMail;
    }

    /**
     * Setter for contactName
     * @param string $contactName
     */
    public function setContactName($contactName)
    {
        $this->contactName = $contactName;
    }

    /**
     * Getter for contactName
     * @return string
     */
    public function getContactName()
    {
        return $this->contactName;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Apply configuration from ContactInfo data object
     * @param ContactInfo $contactInfo
     */
    public function configureByContactInfo(ContactInfo $contactInfo)
    {
        // Test if loaded successfully
        if (!$contactInfo->getObject()) {
            $contactInfo->load();
        }

        $this->setAuthKey($contactInfo->getAuthKey());
        $this->setContactName($contactInfo->getPerson());
        $this->setContactMail($contactInfo->getEmail());
    }

    /**
     * Creates a pre-configured process object for later use
     * @return Process
     */
    public function getProcessObject()
    {
        return $this->processObject;
    }

    /**
     * Prepare process object
     * @throws \TKMON\Exception\ModelException
     */
    public function prepare()
    {
        if (!$this->getType()) {
            throw new ModelException('$type not defined');
        }

        if (!$this->getAuthKey()) {
            throw new ModelException('$authKey not defined');
        }

        if (!$this->getContactMail()) {
            throw new ModelException('$contactMail not defined');
        }

        if (!$this->getContactName()) {
            throw new ModelException('$contactName not defined');
        }

        $this->processObject->addNamedArgument('--type', $this->getType());
        $this->processObject->addNamedArgument('--contact-person', $this->getContactName());
        $this->processObject->addNamedArgument('--contact-mail', $this->getContactMail());
        $this->processObject->addNamedArgument('--auth-key', $this->getAuthKey());

        $this->prepared = true;
    }

    /**
     * Executes the alerter
     * with previously configured parameters
     */
    public function commit()
    {
        if ($this->prepared === false) {
            $this->prepare();
        }

        $this->processObject->execute();
    }
}
