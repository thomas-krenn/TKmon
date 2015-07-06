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

namespace TKMON\Model\Command;

/**
 * Factory to create pre configured commands
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class Factory
{
    /**
     * Namespace
     * if you use auto loading of commands
     * @var string
     */
    private $configNamespace = 'command.config';

    /**
     * Configuration
     * if you use auto loading of commands
     * @var \NETWAYS\Common\Config
     */
    private $config;

    /**
     * Array of commands
     * @var array
     */
    private $commands;

    /**
     * Creates a new factory
     * @param \NETWAYS\Common\Config $config
     */
    public function __construct(\NETWAYS\Common\Config $config = null)
    {
        if ($config) {
            $this->setConfig($config);
        }
    }

    /**
     * Setter for config object
     * @param \NETWAYS\Common\Config $config
     */
    public function setConfig($config)
    {
        $this->config = $config;

        $commands = $this->config->get($this->getConfigNamespace());
        if (is_object($commands)) {
            $this->setCommands($commands);
            return true;
        }

        throw new \TKMON\Exception\ModelException("Commands not found with namespace: ". $this->getConfigNamespace());
    }

    /**
     * Return config object
     * @return \NETWAYS\Common\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Setter for config namespace
     * @param string $configNamespace
     */
    public function setConfigNamespace($configNamespace)
    {
        $this->configNamespace = $configNamespace;
    }

    /**
     * Getter for config namespace
     * @return string
     */
    public function getConfigNamespace()
    {
        return $this->configNamespace;
    }

    /**
     * Setter for commands
     * @param \stdClass $commands
     */
    public function setCommands(\stdClass $commands)
    {
        $this->commands = $commands;
    }

    /**
     * Return definitions set before
     * @return \stdClass
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * Return a predefined command to execute
     * @param $name
     * @return \NETWAYS\IO\Process
     */
    public function create($name)
    {
        $definition = $this->getCommand($name);

        $command = new \NETWAYS\IO\Process($definition->path);

        if (isset($definition->sudo)) {
            $command->setSudoersFlag((bool) $definition->sudo);
        }

        return $command;
    }

    /**
     * Tests if a command exist
     * @param $name
     * @return bool
     */
    private function hasCommand($name)
    {
        return isset($this->commands->{$name});
    }

    /**
     * Return a command definition
     * @param $name
     * @return mixed
     * @throws \TKMON\Exception\ModelException
     */
    private function getCommand($name)
    {
        if ($this->hasCommand($name) === true) {
            $command = $this->commands->$name;

            if (isset($command->path)) {
                return $command;
            }

            throw new \TKMON\Exception\ModelException("Path not defined for command: $name");
        }

        throw new \TKMON\Exception\ModelException("Command not defined: $name");
    }
}
