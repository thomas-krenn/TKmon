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

namespace TKMON\Extension\Host;

use ICINGA\Object\Host;
use NETWAYS\Chain\ReflectionHandler;
use NETWAYS\Common\ArrayObject;
use TKMON\Form\Field\Dummy;
use TKMON\Form\Field\IpAddress;
use TKMON\Form\Field\Text;
use TKMON\Interfaces\ApplicationModelInterface;
use TKMON\Model\Icinga\ServiceData;

/**
 * Extending host creation
 *
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class DefaultAttributes extends ReflectionHandler implements ApplicationModelInterface
{
    /**
     * DI container
     *
     * @var \Pimple
     */
    private $container;

    /**
     * Create a new object extender
     * @param \Pimple $container
     */
    public function __construct(\Pimple $container)
    {
        $this->setContainer($container);
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

    // ------------------------------------------------------------------------
    // COMMAND METHOD API
    // ------------------------------------------------------------------------

    /**
     * Hook for creation
     *
     * Add default attributes from configuration
     *
     * @param \ICINGA\Object\Host $host
     */
    public function commandCreateHost(Host $host)
    {
        $default = $this->container['config']['icinga.record.host'];

        if ($default instanceof \stdClass) {
            $host->fromVoyagerObject($default);
        }
    }

    /**
     * Define default edit attributes
     *
     * @param ArrayObject $attributes
     */
    public function commandDefaultEditableAttributes(ArrayObject $attributes)
    {
        $attributes->fromArray(
            array(
                'host_name' => new Text('host_name', _('Hostname')),
                'alias'     => new Text('alias', _('Alias')),
                'address'   => new IpAddress('address', _('IP address')),

                // Dummy field, just we've the value in voyager chain
                // HTML is made in template and with hostTypeAhead
                'parents'   => new Dummy('parents', _('Parent hosts'), false)
            )
        );
    }

    /**
     * Add a simple ping check to host before creation
     *
     * TODO: Build a catalogue to do this
     *
     * @param \ICINGA\Object\Host $host
     */
    public function commandBeforeHostCreate(Host $host)
    {
        /** @var ServiceData $serviceModel */
        $serviceModel = $this->container['serviceData'];

        // Add ping service to the newly created host
        $service = $serviceModel->createServiceFromCatalogue('net-ping');

        $host->addService($service);
    }
}
