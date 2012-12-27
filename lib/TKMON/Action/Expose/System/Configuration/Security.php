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

namespace TKMON\Action\Expose\System\Configuration;

/**
 * Security settings
 *
 * @package TKMON\Action
 * @author Marius Hein <marius.hein@netways.de>
 */
class Security extends \TKMON\Action\Base
{
    /**
     * Display the html side of basic security settings
     * @return \TKMON\Mvc\Output\TwigTemplate
     */
    public function actionIndex()
    {
        $output = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
        $output->setTemplateName('views/System/Configuration/Security.twig');
        return $output;
    }

    /**
     * Action to trigger password changes
     * @return \TKMON\Mvc\Output\JsonResponse
     */
    public function actionChangePassword()
    {
        $params = $this->container['params'];
        $user = $this->container['user'];

        $r = new \TKMON\Mvc\Output\JsonResponse();

        try {
            $re = $user->changePassword(
                $params->getParameter('current_password'),
                $params->getParameter('password'),
                $params->getParameter('password_verify')
            );
            $r->setSuccess($re);
        } catch (\TKMON\Exception\UserException $e) {
            $r->setSuccess(false);
            $r->addException($e);
        }

        return $r;
    }
}
