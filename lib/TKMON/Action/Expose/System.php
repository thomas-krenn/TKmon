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

namespace TKMON\Action\Expose;

/**
 * System base action
 *
 * @package TKMON\Action
 * @author Marius Hein <marius.hein@netways.de>
 */
class System extends \TKMON\Action\Base
{
    /**
     * Security flag for ping action
     * @return bool
     */
    public function securityPing()
    {
        return false;
    }

    /**
     * Simple ping action to determine we're online
     * @return \TKMON\Mvc\Output\JsonResponse
     */
    public function actionPing()
    {

        $user = $this->container['user'];
        $config = $this->container['config'];

        $response = new \TKMON\Mvc\Output\JsonResponse();
        $response->setSuccess(true);
        $response->addData(array(
            'ping' => true,
            'user' => $user->getAuthenticated() ? $user->getName() : false,
            'version' => $config->get('app.version.release')
        ));

        return $response;
    }
}
