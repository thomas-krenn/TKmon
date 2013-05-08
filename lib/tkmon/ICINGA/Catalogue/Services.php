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

namespace ICINGA\Catalogue;

/**
 * Service catalogue
 *
 * @package ICINGA
 * @author Marius Hein <marius.hein@netways.de>
 */
class Services extends \ICINGA\Base\Catalogue
{
    /**
     * Build a ready to service object
     *
     * @param string $name
     * @return \ICINGA\Object\Service
     */
    public function getItem($name)
    {
        $struct = parent::getItem($name);

        $checkCommand = clone($struct->check_command);
        unset($struct->check_command);

        $arguments = $checkCommand->arguments;
        unset($checkCommand->arguments);

        $catalogueAttributes = clone($struct->_catalogue_attributes);
        unset($struct->_catalogue_attributes);

        /** @var $service \ICINGA\Object\Service */
        $service = \ICINGA\Object\Service::createFromDataVoyager($struct);

        /** @var $command \ICINGA\Object\Command */
        $command = \ICINGA\Object\Command::createFromDataVoyager($checkCommand);

        if (is_array($arguments)) {
            foreach ($arguments as $argument) {
                $commandArgument = \ICINGA\Base\CommandArgument::createFromVoyager($argument);
                $command->addArgument($commandArgument);
            }
        }

        $service->setCommand($command);

        if (isset($catalogueAttributes->tags)) {
            $service->addCustomVariable('tags', implode(', ', $catalogueAttributes->tags));
        }

        foreach (array('name', 'label', 'description') as $attribute) {
            if (isset($catalogueAttributes->{$attribute})) {
                $service->addCustomVariable($attribute, $catalogueAttributes->{$attribute});
            }
        }

        return $service;
    }

    /**
     * Get catalog meta information
     * @param string $name
     * @return \stdClass
     */
    public function getAttributes($name)
    {
        $struct = parent::getItem($name);
        return $struct->_catalogue_attributes;
    }
}
