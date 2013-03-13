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

/**
 * Abstract model for working with our DI container
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class ContactInfo extends \ICINGA\Loader\FileSystem implements \TKMON\Interfaces\ApplicationModelInterface
{

    /**
     * Map data to custom vars before write
     * @var string[]
     */
    private static $dataMap = array(
        'Person'            => 'tkmon_contactperson',
        'Email'             => 'tkmon_contactemail',
        'AuthKey'           => 'tkmon_authkey'
    );

    /**
     * Additional template configuration
     * @var string[]
     */
    private static $defaultAttributes = array(
        'name'              => 'thomas-krenn-host',
        'use'               => 'generic-host',
        'register'          => '0',
        'contact_groups'    => 'tkmon-system,tkmon-admin'
    );

    /**
     * Name of contact person
     *
     * @var string
     */
    private $person;

    /**
     * Email address for contact purposes
     * @var string
     */
    private $email;

    /**
     * Auth key for thomas krenn
     * @var string
     */
    private $authKey;

    /**
     * DI container
     *
     * @var \Pimple
     */
    private $container;

    /**
     * Object name we work on
     * @var string
     */
    private $objectName;

    /**
     * Object to work on
     * @var \ICINGA\Object\Host
     */
    private $object;

    /**
     * Loading strategy
     * @var \ICINGA\Loader\Strategy\SimpleObject
     */
    private $strategy;

    /**
     * Creates a new object
     * @param \Pimple $container
     */
    public function __construct(\Pimple $container)
    {
        $this->setContainer($container);

        $this->strategy = new \ICINGA\Loader\Strategy\SimpleObject();
        $this->setStrategy($this->strategy);

        $this->setPath(
            $this->container['config']['icinga.dir.template']
        );


        $this->setObjectName(
            $this->container['config']['thomaskrenn.template.host']
        );
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
     * Setter for email
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Getter for email
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Setter for person
     * @param string $person
     */
    public function setPerson($person)
    {
        $this->person = $person;
    }

    /**
     * Getter for person
     * @return string
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * Setter for objectName
     * @param string $objectName
     */
    public function setObjectName($objectName)
    {
        $this->objectName = $objectName;
    }

    /**
     * Getter for objectName
     * @return string
     */
    public function getObjectName()
    {
        return $this->objectName;
    }

    /**
     * Setter for object
     * @param \ICINGA\Object\Host $object
     */
    public function setObject(\ICINGA\Object\Host $object)
    {
        $this->object = $object;
    }

    /**
     * Getter for object
     * @return \ICINGA\Object\Host
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Loads the data from config into object
     */
    public function load()
    {
        parent::load();

        $object = $this->get($this->objectName);

        if ($object instanceof \ICINGA\Object\Host) {
            $this->setObject($object);

            foreach (self::$dataMap as $local => $customVar) {
                $setter = 'set'. $local;
                $this->$setter($object->getCustomVariable($customVar));
            }
        } else {
            // Not there, create a new one
            $object = new \ICINGA\Object\Host();
            $object->fromArray(self::$defaultAttributes);
            $this->setObject($object);
            $this[$object->getObjectIdentifier()] = $object; // Own setter strategy ;-)
        }
    }

    /**
     * Writes the data to fs
     */
    public function write()
    {
        $host = $this->getObject();

        foreach (self::$dataMap as $local => $customVar) {
            $getter = 'get'. $local;
            $host->addCustomVariable($customVar, $this->$getter());
        }

        // Write defaults if someone change this
        // or template is missing
        foreach (self::$defaultAttributes as $attribute => $value) {
            $host->{ $attribute } = $value;
        }

        parent::write();
    }
}
