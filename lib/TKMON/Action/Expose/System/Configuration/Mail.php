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
 * Action to handle mail configuration tasks
 *
 * @package TKMON\Action
 * @author Marius Hein <marius.hein@netways.de>
 */
class Mail extends \TKMON\Action\Base
{
    public function actionIndex(\NETWAYS\Common\ArrayObject $params)
    {
        $template = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
        $template->setTemplateName('views/System/Configuration/Mail.twig');
        $this->addTemplateParam('title', _('Mail config'));
        return $template;
    }

    public function actionTestmail(\NETWAYS\Common\ArrayObject $params)
    {
        $response = new \TKMON\Mvc\Output\JsonResponse();

        try {

            /** @var $config \NETWAYS\Common\Config */
            $config = $this->container['config'];

            $validator = new \NETWAYS\Common\ArrayObjectValidator();

            $validator->addValidator(
                'recipient',
                _('Email'),
                FILTER_VALIDATE_EMAIL
            );

            $validator->addValidator(
                'subject',
                _('Mandatory'),
                FILTER_VALIDATE_REGEXP,
                FILTER_FLAG_NONE,
                array(
                    'regexp' => '/^.+$/'
                )
            );

            $validator->addValidator(
                'message',
                _('Mandatory'),
                FILTER_VALIDATE_REGEXP,
                FILTER_FLAG_NONE,
                array(
                    'regexp' => '/^.+$/'
                )
            );

            $validator->validateArrayObject($params);

            $mailer = new \TKMON\Model\Mail\Simple($this->container);
            $mailer->setSender($config->get('mail.sender', 'noreply@tkmon.unconfigured'));
            $mailer->setTo($params->get('recipient'));
            $mailer->setSubject($params->get('subject'));
            $mailer->setContent($params->get('message'));
            $mailer->sendMail();

            $response->setSuccess(true);
        } catch (\Exception $e) {
            $response->setSuccess(false);
            $response->addException($e);
        }

        return $response;
    }

    public function actionConfigure(\NETWAYS\Common\ArrayObject $params)
    {
        $response = new \TKMON\Mvc\Output\JsonResponse();

        try {
            $response->setSuccess(true);
        } catch (Exception $e) {
            $response->setSuccess(false);
            $response->addException($e);
        }

        return $response;
    }
}
