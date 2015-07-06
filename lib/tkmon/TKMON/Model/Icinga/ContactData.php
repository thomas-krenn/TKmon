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

namespace TKMON\Model\Icinga;
use TKMON\Model\System\ShortMessage;
use ICINGA\Object\Contact;

/**
 * Model to get monitoring information
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class ContactData extends \ICINGA\Loader\FileSystem implements \TKMON\Interfaces\ApplicationModelInterface
{
    /**
     * Config key for creating a generic contact
     */
    const RECORD_DEFAULT = 'icinga.record.contact';

    /**
     * Config key for creating a sms feature enabled contact
     */
    const RECORD_SMS = 'icinga.record.contact.sms';

    /**
     * DI Container
     * @var \Pimple
     */
    private $container;

    /**
     * Prefedined strategy object
     * @var \ICINGA\Loader\Strategy\SimpleObject
     */
    private $strategy;

    /**
     * Base record
     *
     * @var \stdClass
     */
    private $contactBaseRecord;

    /**
     * Creates a new object
     * @param \Pimple $container
     */
    public function __construct(\Pimple $container, $smsEnabled = false)
    {
        $this->setContainer($container);

        $this->strategy = new \ICINGA\Loader\Strategy\SimpleObject();
        $this->setStrategy($this->strategy);

        $this->setPath($this->container['config']['icinga.dir.contact']);

        $smsModel = new ShortMessage($container);
        if ($smsEnabled || $smsModel->isEnabled() === true) {
            $this->contactBaseRecord = $container['config'][self::RECORD_SMS];
        } else {
            $this->contactBaseRecord = $container['config'][self::RECORD_DEFAULT];
        }

        $this->setDropAllFlag(true);
    }

    /**
     * Setter for DI container
     * @param \Pimple $container
     */
    public function setContainer(\Pimple $container)
    {
        $this->container = $container;
    }

    /**
     * Getter for DI configuration
     * @return \Pimple
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Returns the contact by name
     * @param string $contactName
     * @return Contact
     * @throws \ICINGA\Exception\AttributeException
     */
    public function getContact($contactName)
    {
        if ($this->offsetExists($contactName)) {
            return $this[$contactName];
        }

        throw new \ICINGA\Exception\AttributeException('Contact does not exist: '. $contactName);
    }

    /**
     * Sets a new contact
     * @param Contact $contact
     * @throws \TKMON\Exception\ModelException
     */
    public function setContact(Contact $contact)
    {

        if ($this->offsetExists($contact->getObjectIdentifier())) {
            throw new \TKMON\Exception\ModelException('Contact already exists: '. $contact->getObjectIdentifier());
        }

        $this[$contact->getObjectIdentifier()] = $contact;
    }

    /**
     * Updates an existing contact
     * @param Contact $contact
     * @throws \TKMON\Exception\ModelException
     */
    public function updateContact(Contact $contact)
    {

        $oid = $contact->getObjectIdentifier();

        if ($this->offsetExists($oid) === false) {
            throw new \TKMON\Exception\ModelException('Contact does not exist: '. $oid);
        }

        $this->offsetUnset($oid);

        // Create new because of updates
        $contact->createObjectIdentifier();

        $this[$contact->getObjectIdentifier()] = $contact;
    }

    /**
     * Creates an contact record from attributes
     * @param \NETWAYS\Common\ArrayObject $attributes
     * @return Contact
     */
    public function createContact(\NETWAYS\Common\ArrayObject $attributes)
    {
        if (isset($attributes['pager']) && strlen($attributes['pager'])) {
            $attributes->fromVoyagerObject($this->contactBaseRecord);
        } else {
            $base = $this->container['config'][self::RECORD_DEFAULT];
            $attributes->fromVoyagerObject($base);
        }
        $record = Contact::createObjectFromArray($attributes);
        return $record;
    }

    /**
     * Remove contact by name
     * @param $name
     * @throws \TKMON\Exception\ModelException
     */
    public function removeContactByName($name)
    {
        if (!$this->offsetExists($name)) {
            throw new \TKMON\Exception\ModelException('Contact does not exist: '. $name);
        }

        $this->offsetUnset($name);
    }

    /**
     * Recreate base attributes for all records
     *
     * @throws \TKMON\Exception\ModelException
     */
    public function resetBaseRecord()
    {
        foreach ($this->getAll() as $contact) {
            $record = $this->createContact($contact);
            $this->updateContact($record);
        }
    }
}
