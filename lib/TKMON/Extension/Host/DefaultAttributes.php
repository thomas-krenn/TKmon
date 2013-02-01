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

/**
 * Extending host creation
 *
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class DefaultAttributes extends \NETWAYS\Chain\ReflectionHandler implements \TKMON\Interfaces\ApplicationModelInterface
{
    /**
     * @var \Pimple
     */
    private $container;

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

    public function commandCreateHost(\ICINGA\Object\Host $host)
    {
        $default = $this->container['config']['icinga.record.host'];

        if ($default instanceof \stdClass) {
            $host->mergeStdClass($default);
        }
    }

    public function commandDefaultEditableAttributes(\NETWAYS\Common\ArrayObject $attributes)
    {
        $attributes->fromArray(array(
            'host_name' => new \TKMON\Form\Field\Text('host_name', _('Hostname')),
            'alias'     => new \TKMON\Form\Field\Text('alias', _('Alias')),
            'address'   => new \TKMON\Form\Field\Text('address', _('IP address'))
        ));
    }

    /**
     * Add a simple ping check to host before creation
     *
     * @param \ICINGA\Object\Host $host
     */
    public function commandBeforeHostCreate(\ICINGA\Object\Host $host)
    {
        $serviceModel = new \TKMON\Model\Icinga\ServiceData($this->container);

        $pingConfiguration = new \NETWAYS\Common\ArrayObject(
            array(
                'service_description'   => 'net-ping',
                'check_command'         => 'check_ping!20,20%!40,40%'
            )
        );

        $service = $serviceModel->createService($pingConfiguration);

        $host->addService($service);
    }
}
