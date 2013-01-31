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
class HostData extends \ICINGA\Loader\FileSystem implements \TKMON\Interfaces\ApplicationModelInterface
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
     * Create a new object
     * @param \Pimple $container
     */
    public function __construct(\Pimple $container)
    {
        $this->setContainer($container);

        $this->strategy = new \ICINGA\Loader\Strategy\HostServiceObjects();
        $this->setStrategy($this->strategy);
        $this->setPath($this->container['config']['icinga.dir.host']);
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
}
