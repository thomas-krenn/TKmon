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

use NETWAYS\Common\ArrayObject;
use NETWAYS\Common\ArrayObjectValidator;
use NETWAYS\Common\ValidatorObject;
use TKMON\Action\Base;
use TKMON\Exception\UserException;
use TKMON\Model\User;
use TKMON\Mvc\Output\JsonResponse;
use TKMON\Mvc\Output\TwigTemplate;

/**
 * Action handling login front interactions
 *
 * @package TKMON\Action
 * @author Marius Hein <marius.hein@netways.de>
 */
class Login extends Base
{

    /**
     * Security flag for index action
     * @return bool
     */
    public function securityIndex()
    {
        return false;
    }

    /**
     * Show login box
     * @param ArrayObject $params
     * @return TwigTemplate
     */
    public function actionIndex(ArrayObject $params)
    {
        $validator = new ArrayObjectValidator();
        $validator->addValidatorObject(
            ValidatorObject::create(
                'referrer',
                'Referrer URL',
                ValidatorObject::VALIDATE_ANYTHING,
                null,
                null,
                false
            )
        );

        $validator->validateArrayObject($params);

        $output = new TwigTemplate($this->container['template']);
        $output->setTemplateName('forms/login.twig');
        $output['referrer'] = $params->get('referrer');
        return $output;
    }

    /**
     * Security flag for logout;
     * @return bool
     */
    public function securityLogin()
    {
        return false;
    }

    /**
     * Login request as ajax
     * @param ArrayObject $params
     * @return JsonResponse
     */
    public function actionLogin(ArrayObject $params)
    {
        /** @var User $user */
        $user = $this->container['user'];

        $logger = $this->container['logger'];

        $r = new JsonResponse();

        $logger->info('Starting login for user: '. $params->get('username', 'NONE'));

        try {
            $user->doAuthenticate($params->get('username'), $params->get('password'));
            $user->write();

            $this->container['config']['app.login.counter'] += 1;

            $r->setSuccess(true);

            $logger->warn('User logged in: '. $user->getName());
        } catch (UserException $e) {
            $r->setSuccess(false);
            $r->addException($e);

            $logger->error('User login failed for user: '. $params->get('username', 'NONE'));
        }


        $r['authenticated'] = $user->getAuthenticated();
        return $r;
    }

    /**
     * Logout request
     * @param ArrayObject $params
     * @return TwigTemplate
     */
    public function actionLogout(ArrayObject $params)
    {
        /** @var User $user */
        $user = $this->container['user'];

        /** @var \Logger $logger */
        $logger = $this->container['logger'];

        $logger->warn('User logged out successfully: '. $user->getName());

        $session = $this->container['session'];
        $session->destroySession();

        /** @var $navigation \TKMON\Navigation\Container */
        // $navigation = $this->container['navigation'];
        // $navigation->invalidateCache();

        $template = new TwigTemplate($this->container['template']);
        $template->setTemplateName('forms/logout.twig');

        return $template;
    }
}
