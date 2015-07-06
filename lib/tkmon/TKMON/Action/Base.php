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

namespace TKMON\Action;

/**
 * Base class for frontend actions
 *
 * @package TKMON\Action
 * @author Marius Hein <marius.hein@netways.de>
 */
abstract class Base
{

    /**
     * Parameter holder for to configure outer template
     * @var array
     */
    private $templateParams = array();

    /**
     * Our DI container
     * @var \Pimple
     */
    protected $container;

    /**
     * Setter for DI container
     * @param \Pimple $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * Getter for DI container
     * @return \Pimple
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Return the parameter holder
     * @return \NETWAYS\Http\CgiParams
     */
    protected function getParameterHolder()
    {
        return $this->container['params'];
    }

    /**
     * Initialize method after action is configured
     */
    public function init()
    {
        // DO NOTHING HERE
    }

    /**
     * Setter for templateParams
     *
     * Set the whole array
     *
     * @param array $templateParams
     */
    public function setTemplateParams(array $templateParams)
    {
        $this->templateParams = $templateParams;
    }

    /**
     * Getter for templateParams
     * @return array
     */
    public function getTemplateParams()
    {
        return $this->templateParams;
    }

    /**
     * Add item to params
     *
     * @param string $paramName
     * @param mixed $paramValue
     */
    public function addTemplateParam($paramName, $paramValue)
    {
        $this->templateParams[$paramName] = $paramValue;
    }

    /**
     * Remove single item from params
     *
     * @param string $paramName
     */
    public function removeTemplateParam($paramName)
    {
        if (isset($this->templateParams[$paramName])) {
            unset($this->templateParams[$paramName]);
        }
    }

    /**
     * Drop all params and start new
     */
    public function purgeTemplateParams()
    {
        unset($this->templateParams);
        $this->templateParams = array();
    }
}
