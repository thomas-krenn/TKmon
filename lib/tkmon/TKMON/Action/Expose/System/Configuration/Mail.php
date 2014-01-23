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

use TKMON\Model\ThomasKrenn\ContactInfo;

/**
 * Action to handle mail configuration tasks
 *
 * @package TKMON\Action
 * @author Marius Hein <marius.hein@netways.de>
 */
class Mail extends \TKMON\Action\Base
{
    /**
     * Render index page
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\TwigTemplate
     */
    public function actionIndex(\NETWAYS\Common\ArrayObject $params)
    {
        $template = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
        $template->setTemplateName('views/System/Configuration/Mail.twig');
        $this->addTemplateParam('title', _('Mail config'));

        $postfixModel = new \TKMON\Model\Mail\Postfix($this->container);
        $postfixModel->load();
        $template['relayhost'] = $postfixModel->getRelayHost();

        $senderModel = new \TKMON\Model\Mail\Sender($this->container);
        $template['sender'] = $senderModel->getSender();

        return $template;
    }

    /**
     * Ajax action to send a mail
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\JsonResponse
     */
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
                \NETWAYS\Common\ArrayObjectValidator::VALIDATE_MANDATORY
            );

            $validator->addValidator(
                'message',
                _('Mandatory'),
                \NETWAYS\Common\ArrayObjectValidator::VALIDATE_MANDATORY
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

    /**
     * Ajax action to configure mail settings
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\JsonResponse
     */
    public function actionConfigure(\NETWAYS\Common\ArrayObject $params)
    {
        $response = new \TKMON\Mvc\Output\JsonResponse();

        try {
            $validator = new \NETWAYS\Common\ArrayObjectValidator();
            $validator->addValidator('sender', 'Email address', FILTER_VALIDATE_EMAIL);

            // Only validate if parameter exists
            if ($params->get('relayhost')) {
                $validator->addValidator('relayhost', _('IP'), FILTER_VALIDATE_IP);
            }

            $validator->validateArrayObject($params);

            $contactInfoModel = new ContactInfo($this->container);

            $senderModel = new \TKMON\Model\Mail\Sender($this->container);
            $senderModel->setContactInfo($contactInfoModel);
            $senderModel->setSender($params->get('sender'));

            $postfixModel = new \TKMON\Model\Mail\Postfix($this->container);
            $postfixModel->load();
            $postfixModel->setRelayHost($params->get('relayhost', ''));
            $postfixModel->write();

            $systemModel = new \TKMON\Model\System($this->container);
            $systemModel->restartPostfix();

            $response->setSuccess(true);
        } catch (\Exception $e) {
            $response->setSuccess(false);
            $response->addException($e);
        }

        return $response;
    }
}
