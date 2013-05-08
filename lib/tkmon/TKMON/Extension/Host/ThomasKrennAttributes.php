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
use TKMON\Interfaces\ApplicationModelInterface;

/**
 * Attributes for ThomasKrenn products
 *
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class ThomasKrennAttributes extends \NETWAYS\Chain\ReflectionHandler implements ApplicationModelInterface
{
    /**
     * DI container
     * @var \Pimple
     */
    private $container;

    /**
     * Create a new object
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

    /**
     * Adding ThomasKrenn specific attributes to mask
     * @param \NETWAYS\Common\ArrayObject $attributes
     */
    public function commandDefaultCustomVariables(\NETWAYS\Common\ArrayObject $attributes)
    {
        $attributes->fromArray(
            array(
                'serial'          => new \TKMON\Form\Field\Text('serial', _('Serial')), // Mandatory for tkalert
                'os'              => new \TKMON\Form\Field\Text('os', _('Operating system')), // Mandatory for tkalert
                'ipmi_ip'         => new \TKMON\Form\Field\Text('ipmi_ip', _('IPMI IP address'), false),
                'ipmi_user'       => new \TKMON\Form\Field\Text('ipmi_user', _('IPMI user'), false),
                'ipmi_password'   => new \TKMON\Form\Field\Text('ipmi_password', _('IPMI password'), false),
                'snmp_community'  => new \TKMON\Form\Field\Text('snmp_community', _('SNMP community'), false)
            )
        );
    }

    /**
     * Command hook before a host is created
     * @param Host $host
     */
    public function commandBeforeHostCreate(Host $host)
    {
        $this->updateHostTemplate($host);
    }

    /**
     * Command hook before a host is update
     * @param Host $host
     */
    public function commandBeforeHostUpdate(Host $host)
    {
        $this->updateHostTemplate($host);
    }

    /**
     * Change used template to use tk service
     *
     * Dispatch function for different commands
     *
     * @param Host $host
     */
    public function updateHostTemplate(Host $host)
    {
        $host->setUse($this->container['config']['thomaskrenn.icinga.template.host']);
    }
}
