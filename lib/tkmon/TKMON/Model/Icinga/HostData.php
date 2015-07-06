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

use ICINGA\Interfaces\LoaderStrategyInterface;
use ICINGA\Loader\FileSystem;
use ICINGA\Loader\Strategy\HostServiceObjects;
use ICINGA\Object\Host;
use NETWAYS\Chain\Command;
use NETWAYS\Chain\Exception\HandlerException;
use NETWAYS\Chain\Interfaces\CommandInterface;
use NETWAYS\Chain\Interfaces\HandlerInterface;
use NETWAYS\Chain\Interfaces\ManagerInterface;
use NETWAYS\Common\ArrayObject;
use NETWAYS\Common\ArrayObjectValidator;
use TKMON\Exception\ModelException;
use TKMON\Interfaces\ApplicationModelInterface;

/**
 * Model handle host creation
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class HostData extends FileSystem implements ApplicationModelInterface,
ManagerInterface
{
    /**
     * DI container
     * @var \Pimple
     */
    private $container;

    /**
     * Load strategy
     * @var LoaderStrategyInterface
     */
    private $strategy;

    /**
     * All command handler registered
     * @var \SplObjectStorage
     */
    private $handlers;

    /**
     * Flag if we throw attached handler errors immediately
     * @var bool
     */
    private $stopOnFirstHandlerException=false;

    /**
     * Create a new object
     * @param \Pimple $container
     */
    public function __construct(\Pimple $container)
    {
        $this->setContainer($container);

        $this->handlers = new \SplObjectStorage();

        $this->strategy = new HostServiceObjects();
        $this->setStrategy($this->strategy);
        $this->setPath($this->container['config']['icinga.dir.host']);

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
     * Add a handler to chain
     *
     * @param HandlerInterface $handler
     * @return void
     */
    public function appendHandlerToChain(HandlerInterface $handler)
    {
        $this->handlers->attach($handler);
    }

    /**
     * Remove handler from chain
     * @param HandlerInterface $handler
     * @return void
     */
    public function removeHandlerFromChain(HandlerInterface $handler)
    {
        $this->handlers->detach($handler);
    }

    /**
     * Run the request
     * @param CommandInterface $command
     * @throws mixed
     * @throws HandlerException
     * @return boolean
     */
    public function processRequest(CommandInterface $command)
    {
        /** @var $handler HandlerInterface */
        $handler = null;

        /** @var $handlerExceptions array|\Exception */
        $handlerExceptions = array();
        foreach ($this->handlers as $handler) {
            try {
                $handler->processRequest($command);
            } catch (HandlerException $e) {
                if ($this->stopOnFirstHandlerException === true) {
                    throw $e;
                } else {
                    $handlerExceptions[] = $e;
                }
            }
        }

        if (count($handlerExceptions)) {
            $exception = array_pop($handlerExceptions);
            throw $exception;
        }
    }

    /**
     * Configure the chain what happens on error
     * @param boolean $flag
     * @return void
     */
    public function stopOnFirstHandlerException($flag)
    {
        $this->stopOnFirstHandlerException = (bool)$flag;
    }

    /**
     * Call a command.
     *
     * Additional arguments are allowed to distribute to
     * object neighbours
     *
     * @param string $commandName
     */
    protected function callCommand($commandName)
    {
        $arguments = func_get_args();
        $commandName = array_shift($arguments);

        $command = new Command($commandName);
        $command->fromArray($arguments);
        $this->processRequest($command); // Make the request
    }

    /**
     * Return default attributes
     * @return ArrayObject
     */
    public function getEditableAttributes()
    {
        $attributes = new ArrayObject();

        // Notify others
        $this->callCommand('defaultEditableAttributes', $attributes);

        /** @var $attribute \TKMON\Form\Field */
        $attribute = null;
        foreach ($attributes as $attribute) {
            $attribute->setTemplate($this->container['template']);
        }

        return $attributes;
    }

    /**
     * Return a list of editable custom variables to add
     * @return ArrayObject
     */
    public function getCustomVariables()
    {
        $attributes = new ArrayObject();

        // Notify others
        $this->callCommand('defaultCustomVariables', $attributes);

        /** @var $attribute \TKMON\Form\Field */
        $attribute = null;
        foreach ($attributes as $attribute) {
            $attribute->setTemplate($this->container['template']);
            $attribute->setNamePrefix('cf_');
        }

        return $attributes;
    }


    /**
     * Create a validator
     *
     * Build from main attributes and custom variables
     *
     * @return ArrayObjectValidator
     */
    public function createValidator()
    {
        $validator = new ArrayObjectValidator();

        /** @var $field \TKMON\Form\Field */
        $field = null;

        foreach ($this->getEditableAttributes() as $field) {
            $validator->addValidatorObject($field->getValidator());
        }

        foreach ($this->getCustomVariables() as $field) {
            $validator->addValidatorObject($field->getValidator());
        }

        // Notify others
        $this->callCommand('createValidator', $validator);

        return $validator;
    }

    /**
     * Write data to fs
     *
     * But call our object neighbours before
     */
    public function write()
    {
        $this->callCommand('beforeWrite', $this);
        parent::write();
        $this->callCommand('write', $this);
    }

    // ------------------------------------------------------------------------
    // Data api
    // ------------------------------------------------------------------------

    /**
     * Creates a host
     * @param ArrayObject $attributes
     * @return Host
     */
    public function createHost(ArrayObject $attributes)
    {

        $default = $this->container['config']['icinga.record.host'];
        $attributes->fromVoyagerObject($default);

        /** @var Host $record */
        $record = Host::createObjectFromArray($attributes);

        $this->callCommand('createHost', $record);

        return $record;
    }

    /**
     * Test all configured parent hosts for their existence
     * @param Host $host
     * @throws ModelException
     */
    private function testHostParents(Host $host)
    {
        if ($host->parents) {
            $parents = explode(',', $host->parents);
            foreach ($parents as $parent) {
                try {
                    $this->getHost($parent);
                } catch (ModelException $e) {
                    throw new ModelException(
                        'Parent host "'. $parent. '" does not exist'
                    );
                }
            }
        }
    }

    /**
     * Update a host
     * @param Host $host
     * @throws ModelException
     */
    public function updateHost(Host $host)
    {

        $oid = $host->getObjectIdentifier();

        if ($this->offsetExists($oid) === false) {
            throw new ModelException('Host does not exist: '. $oid);
        }

        $this->callCommand('beforeHostUpdate', $host);

        $this->testHostParents($host);

        $this[$oid] = $host;

        $this->callCommand('hostUpdate', $host);
    }

    /**
     * Create a new host
     * @param Host $host
     * @throws ModelException
     */
    public function setHost(Host $host)
    {
        $oid = $host->getObjectIdentifier();

        if ($this->offsetExists($oid)) {
            throw new ModelException('Host exists: '. $oid);
        }

        $this->callCommand('beforeHostCreate', $host);

        $this->testHostParents($host);

        $this[$oid] = $host;

        $this->callCommand('hostCreate', $host);
    }

    /**
     * Get a host
     * @param string $identifier host_name attribute
     * @return Host
     * @throws ModelException
     */
    public function getHost($identifier)
    {
        if ($this->offsetExists($identifier) === false) {
            throw new ModelException('Host does not exist: '. $identifier);
        }

        /** @var $host Host */
        return $this->offsetGet($identifier);
    }

    /**
     * Remove a host by host_name
     * @param string $identifier host_name attribute
     * @throws ModelException
     */
    public function removeHostByName($identifier)
    {
        if ($this->offsetExists($identifier) === false) {
            throw new ModelException('Host does not exist: '. $identifier);
        }

        $this->offsetUnset($identifier);
    }

    /**
     * Return a couple of hosts
     *
     * @param string $query query
     * @return Host
     */
    public function searchHost($query)
    {
        $query = strtolower($query);
        $cb = function ($item) use ($query) {

            if (!($item instanceof Host)) {
                return false;
            }

            return
                strpos(strtolower($item->hostName), $query) !== false
                || strpos(strtolower($item->alias), $query) !== false
                || strpos(strtolower($item->displayName), $query) !== false
                || strpos(strtolower($item->address), $query) !== false;
        };

        return array_filter($this->getArrayCopy(), $cb);
    }
}
