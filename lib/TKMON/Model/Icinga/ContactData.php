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

namespace TKMON\Model\Icinga;

/**
 * Model to get monitoring information
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class ContactData extends \ICINGA\Loader\FileSystem implements \TKMON\Interfaces\ApplicationModelInterface
{
    private $container;

    private $strategy;

    public function __construct(\Pimple $container)
    {
        $this->setContainer($container);

        $this->strategy = new \ICINGA\Loader\Strategy\SimpleObject();
        $this->setStrategy($this->strategy);

        $this->setPath($this->container['config']['icinga.dir.contact']);


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

    public function getContact($contactName)
    {
        if ($this->offsetExists($contactName)) {
            return $this[$contactName];
        }

        throw new \ICINGA\Exception\AttributeException('Contact does not exist: '. $contactName);
    }

    public function setContact(\ICINGA\Object\Contact $contact)
    {

        if ($this->offsetExists($contact->getObjectIdentifier())) {
            throw new \TKMON\Exception\ModelException('Contact already exists: '. $contact->getObjectIdentifier());
        }

        $this[$contact->getObjectIdentifier()] = $contact;
    }

    public function updateContact(\ICINGA\Object\Contact $contact)
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

    public function createContact(\NETWAYS\Common\ArrayObject $attributes)
    {
        $default = $this->container['config']['icinga.record.contact'];
        $attributes->mergeStdClass($default);
        $record = \ICINGA\Object\Contact::createObjectFromArray($attributes);

        return $record;
    }

    public function removeContactByName($name)
    {
        if (!$this->offsetExists($name)) {
            throw new \TKMON\Exception\ModelException('Contact does not exist: '. $name);
        }

        $this->offsetUnset($name);
    }

}
