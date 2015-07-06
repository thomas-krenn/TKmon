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

namespace TKMON\Extension\Service;

use ICINGA\Object\Service;
use NETWAYS\Common\ArrayObject;
use TKMON\Exception\ModelException;
use TKMON\Interfaces\ApplicationModelInterface;

/**
 * Controls notification handling
 *
 * When a service is created or updated
 *
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class ThomasKrennNotification extends \NETWAYS\Chain\ReflectionHandler implements ApplicationModelInterface
{

    /**
     * DI container
     * @var \Pimple
     */
    private $container;

    /**
     * Create a new notification modifier object
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
     * Service creation hook
     *
     * Decide which notification is used
     *
     * @param Service $service
     * @param ArrayObject $params
     * @throws \TKMON\Exception\ModelException
     */
    public function commandBeforeServiceWrite(Service $service, ArrayObject $params)
    {
        if ($params->offsetExists('tk_notify')) {

            if ((boolean)$params['tk_notify'] === true) {
                $template = $this->container['config']['thomaskrenn.template.service'];

                if (!$template) {
                    throw new ModelException('Service template not defined in config (thomaskrenn.template.service)');
                }

                $service->setUse($template);
            }
        }
    }
}
