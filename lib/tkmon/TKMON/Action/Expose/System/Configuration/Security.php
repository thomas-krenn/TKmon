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
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\TwigTemplate
     */
    public function actionIndex(\NETWAYS\Common\ArrayObject $params)
    {
        $output = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
        $output->setTemplateName('views/System/Configuration/Security.twig');

        $user = $this->container['user'];
        $output['system_enabled'] = $user->getSystemAccess();
        $output['user'] = $user;

        $directoryAccess = new \TKMON\Model\Apache\DirectoryAccess($this->container);
        $directoryAccess->load();
        $output['icinga_enabled'] = (int)$directoryAccess->publicAccess();

        return $output;
    }

    /**
     * Action to trigger password changes
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\JsonResponse
     */
    public function actionChangePassword(\NETWAYS\Common\ArrayObject $params)
    {
        /** @var $user \TKMON\Model\User */
        $user = $this->container['user'];

        $r = new \TKMON\Mvc\Output\JsonResponse();

        try {
            $re = $user->changePassword(
                $params->get('current_password'),
                $params->get('password'),
                $params->get('password_verify')
            );
            $r->setSuccess($re);
        } catch (\Exception $e) {
            $r->setSuccess(false);
            $r->addException($e);
        }

        return $r;
    }

    /**
     * Return a html fragment
     * which indicates if the user has system access
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\SimpleString
     */
    public function actionSystemAccess(\NETWAYS\Common\ArrayObject $params)
    {
        $user = $this->container['user'];

        if ($user->getSystemAccess() === true) {
            return  new \TKMON\Mvc\Output\SimpleString(
                '<span class="label label-success">'. _('Enabled'). '</span>'
            );
        }

        return  new \TKMON\Mvc\Output\SimpleString(
            '<span class="label label-important">'. _('Disabled'). '</span>'
        );

    }

    /**
     * Changer for system controll access
     * @param \NETWAYS\Common\ArrayObject $params
     * @throws \TKMON\Exception\ModelException
     * @return \TKMON\Mvc\Output\JsonResponse
     */
    public function actionChangeSystemAccess(\NETWAYS\Common\ArrayObject $params)
    {
        $user = $this->container['user'];
        $val = $params->get('access');
        $response = new \TKMON\Mvc\Output\JsonResponse();

        try {
            if ($val === "1") {
                $user->controlSystemAccess(true);
                $response->setSuccess(true);
            } elseif ($val === "0") {
                $user->controlSystemAccess(false);
                $response->setSuccess(true);
            } else {
                throw new \TKMON\Exception\ModelException(
                    "Invalid arguments, access have to be 0/1"
                );
            }
        } catch (\Exception $e) {
            $response->setSuccess(false);
            $response->addException($e);
        }

        return $response;
    }

    /**
     * Json API endpoint
     *
     * Change icinga interface access
     *
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\JsonResponse
     */
    public function actionChangeIcingaAccess(\NETWAYS\Common\ArrayObject $params)
    {
        $directoryAccess = new \TKMON\Model\Apache\DirectoryAccess($this->container);
        $response = new \TKMON\Mvc\Output\JsonResponse();

        try {
            $directoryAccess->load();

            $validator = new \NETWAYS\Common\ArrayObjectValidator();

            $validator->addValidator(
                'icinga-access',
                'flag 0/1',
                FILTER_VALIDATE_REGEXP,
                null,
                array(
                    'regexp' => '/^(0|1)$/i'
                )
            );

            $validator->validateArrayObject($params);

            if ($params->get('icinga-access', 0) == 1) {
                $directoryAccess->allowAll();
            } else {
                $directoryAccess->allowLocalhostOnly();
            }

            $directoryAccess->write();

            $system = new \TKMON\Model\System($this->container);
            $system->restartApache();

            $response->addData((int)$directoryAccess->publicAccess());
            $response->setSuccess(true);

        } catch (\Exception $e) {
            $response->addException($e);
        }

        return $response;
    }
}
