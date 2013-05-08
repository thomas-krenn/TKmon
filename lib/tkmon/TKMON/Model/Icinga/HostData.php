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
 * Model handle host creation
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class HostData extends \ICINGA\Loader\FileSystem implements \TKMON\Interfaces\ApplicationModelInterface,
\NETWAYS\Chain\Interfaces\ManagerInterface
{
    /**
     * DI container
     * @var \Pimple
     */
    private $container;

    /**
     * Load strategy
     * @var \ICINGA\Interfaces\LoaderStrategyInterface
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

        $this->strategy = new \ICINGA\Loader\Strategy\HostServiceObjects();
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
     * @param \NETWAYS\Chain\Interfaces\HandlerInterface $handler
     * @return void
     */
    public function appendHandlerToChain(\NETWAYS\Chain\Interfaces\HandlerInterface $handler)
    {
        $this->handlers->attach($handler);
    }

    /**
     * Remove handler from chain
     * @param \NETWAYS\Chain\Interfaces\HandlerInterface $handler
     * @return void
     */
    public function removeHandlerFromChain(\NETWAYS\Chain\Interfaces\HandlerInterface $handler)
    {
        $this->handlers->detach($handler);
    }

    /**
     * Run the request
     * @param \NETWAYS\Chain\Interfaces\CommandInterface $command
     * @throws mixed
     * @throws \NETWAYS\Chain\Exception\HandlerException
     * @return boolean
     */
    public function processRequest(\NETWAYS\Chain\Interfaces\CommandInterface $command)
    {
        /** @var $handler \NETWAYS\Chain\Interfaces\HandlerInterface */
        $handler = null;

        /** @var $handlerExceptions array|\Exception */
        $handlerExceptions = array();
        foreach ($this->handlers as $handler) {
            try {
                $handler->processRequest($command);
            } catch (\NETWAYS\Chain\Exception\HandlerException $e) {
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

        $command = new \NETWAYS\Chain\Command($commandName);
        $command->fromArray($arguments);
        $this->processRequest($command); // Make the request
    }

    /**
     * Return default attributes
     * @return \NETWAYS\Common\ArrayObject
     */
    public function getEditableAttributes()
    {
        $attributes = new \NETWAYS\Common\ArrayObject();

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
     * @return \NETWAYS\Common\ArrayObject
     */
    public function getCustomVariables()
    {
        $attributes = new \NETWAYS\Common\ArrayObject();

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
     * @return \NETWAYS\Common\ArrayObjectValidator
     */
    public function createValidator()
    {
        $validator = new \NETWAYS\Common\ArrayObjectValidator();

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
     * @param \NETWAYS\Common\ArrayObject $attributes
     * @return \ICINGA\Object\Host
     */
    public function createHost(\NETWAYS\Common\ArrayObject $attributes)
    {

        $default = $this->container['config']['icinga.record.host'];
        $attributes->fromVoyagerObject($default);

        $record = \ICINGA\Object\Host::createObjectFromArray($attributes);

        $this->callCommand('createHost', $record);

        return $record;
    }

    /**
     * Update a host
     * @param \ICINGA\Object\Host $host
     * @throws \TKMON\Exception\ModelException
     */
    public function updateHost(\ICINGA\Object\Host $host)
    {

        $oid = $host->getObjectIdentifier();

        if ($this->offsetExists($oid) === false) {
            throw new \TKMON\Exception\ModelException('Host does not existt: '. $oid);
        }

        $this->callCommand('beforeHostUpdate', $host);

        $this[$oid] = $host;

        $this->callCommand('hostUpdate', $host);
    }

    /**
     * Create a new host
     * @param \ICINGA\Object\Host $host
     * @throws \TKMON\Exception\ModelException
     */
    public function setHost(\ICINGA\Object\Host $host)
    {
        $oid = $host->getObjectIdentifier();

        if ($this->offsetExists($oid)) {
            throw new \TKMON\Exception\ModelException('Host exists: '. $oid);
        }

        $this->callCommand('beforeHostCreate', $host);

        $this[$oid] = $host;

        $this->callCommand('hostCreate', $host);
    }

    /**
     * Get a host
     * @param string $identifier host_name attribute
     * @return \ICINGA\Object\Host
     * @throws \TKMON\Exception\ModelException
     */
    public function getHost($identifier)
    {
        if ($this->offsetExists($identifier) === false) {
            throw new \TKMON\Exception\ModelException('Host does not exist: '. $identifier);
        }

        /** @var $host \ICINGA\Object\Host */
        return $this->offsetGet($identifier);
    }

    /**
     * Remove a host by host_name
     * @param string $identifier host_name attribute
     * @throws \TKMON\Exception\ModelException
     */
    public function removeHostByName($identifier)
    {
        if ($this->offsetExists($identifier) === false) {
            throw new \TKMON\Exception\ModelException('Host does not exist: '. $identifier);
        }

        $this->offsetUnset($identifier);
    }

    /**
     * Return a couple of hosts
     *
     * @param string $query query
     * @return \ICINGA\Object\Host[]
     */
    public function searchHost($query)
    {
        $query = strtolower($query);
        $cb = function ($item) use ($query) {

            if (!($item instanceof \ICINGA\Object\Host)) {
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
