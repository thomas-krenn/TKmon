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
 * Model handle service creation
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class ServiceData extends \TKMON\Model\ApplicationModel
{
    // ------------------------------------------------------------------------
    // Data api
    // ------------------------------------------------------------------------

    /**
     * Creates a service
     *
     * - From catalogue calues
     * - Add defaults from config
     *
     * @param string $catalogueName
     * @return \ICINGA\Object\Service
     * @throws \TKMON\Exception\ModelException
     */
    public function createServiceFromCatalogue($catalogueName)
    {
        /** @var $catalogue \ICINGA\Catalogue\Services */
        $catalogue = $this->container['serviceCatalogue'];

        $service = $catalogue->getItem($catalogueName);

        if ($service instanceof \ICINGA\Object\Service) {
            $default = $this->container['config']['icinga.record.service'];
            $service->fromVoyagerObject($default);
            return $service;
        }

        throw new \TKMON\Exception\ModelException('Service not found in catalogue: '. $catalogueName);
    }
}
