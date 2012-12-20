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

namespace TKMON\Action\Expose\System;

/**
 * Action handling login front interactions
 *
 * @package TKMON\Action
 * @author Marius Hein <marius.hein@netways.de>
 */
class Login extends \TKMON\Action\Base
{

    /**
     * Show login box
     * @return \TKMON\Mvc\Output\TwigTemplate
     */
    public function actionIndex()
    {
        $output = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
        $output->setTemplateName('forms/login.twig');
        return $output;
    }

    /**
     * Login request as ajax
     * @return \TKMON\Mvc\Output\JsonResponse
     */
    public function actionLogin()
    {
        $user = $this->container['user'];

        $r = new \TKMON\Mvc\Output\JsonResponse();

        try {
            $user->doAuthenticate($this->getParameter('username'), $this->getParameter('password'));
            $user->write();
            $r->setSuccess(true);
        } catch (\TKMON\Exception\UserException $e) {
            $r->setSuccess(false);
            $r->addException($e);
        }


        $r['authenticated'] = $user->getAuthenticated();
        return $r;
    }

    /**
     * Logout request
     * @return \TKMON\Mvc\Output\TwigTemplate
     */
    public function actionLogout()
    {
        $session = $this->container['session'];
        $session->destroySession();

        $template = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
        $template->setTemplateName('forms/logout.twig');
        return $template;
    }
}
