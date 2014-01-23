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
 * @copyright 2012-2014 NETWAYS GmbH <info@netways.de>
 */

namespace TKMON\Action\Expose\ThomasKrenn\Alert;

use NETWAYS\Common\ArrayObjectValidator;
use TKMON\Model\ThomasKrenn\Alert;
use TKMON\Model\ThomasKrenn\ContactInfo;
use TKMON\Mvc\Output\JsonResponse;

/**
 * Configure Settings for ThomasKrenn Alert Generator
 *
 * @package TKMON\Action
 * @author Marius Hein <marius.hein@netways.de>
 */
class Configuration extends \TKMON\Action\Base
{
    /**
     * Show main index for ThomasKrenn alert settings
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\TwigTemplate
     */
    public function actionIndex(\NETWAYS\Common\ArrayObject $params)
    {
        $template = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
        $template->setTemplateName('views/ThomasKrenn/Alert/Configuration.twig');

        $contactInfo = new \TKMON\Model\ThomasKrenn\ContactInfo($this->container);
        $contactInfo->load();

        $template['authkey'] = $contactInfo->getAuthKey();
        $template['email'] = $contactInfo->getEmail();
        $template['person'] = $contactInfo->getPerson();
        $template['enabled'] = ($contactInfo->getEnabledFlag() === true) ? 1 : 0;

        return $template;
    }

    /**
     * Ajax endpoint to write ThomasKrenn alert configuration
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\JsonResponse
     */
    public function actionUpdate(\NETWAYS\Common\ArrayObject $params)
    {
        $response = new \TKMON\Mvc\Output\JsonResponse();
        try {

            $validator = new \NETWAYS\Common\ArrayObjectValidator();

            $validator->addValidatorObject(
                \NETWAYS\Common\ValidatorObject::create(
                    'person',
                    _('Person'),
                    \NETWAYS\Common\ValidatorObject::VALIDATE_MANDATORY
                )
            );

            $validator->addValidatorObject(
                \NETWAYS\Common\ValidatorObject::create(
                    'email',
                    _('email'),
                    FILTER_VALIDATE_EMAIL
                )
            );

            $validator->addValidatorObject(
                \NETWAYS\Common\ValidatorObject::create(
                    'authkey',
                    _('Auth key'),
                    \NETWAYS\Common\ValidatorObject::VALIDATE_MANDATORY
                )
            );

            $validator->addValidatorObject(
                \NETWAYS\Common\ValidatorObject::create(
                    'enabled',
                    _('Enabled flag'),
                    \NETWAYS\Common\ValidatorObject::VALIDATE_MANDATORY
                )
            );

            $validator->validateArrayObject($params);

            $contactInfo = new \TKMON\Model\ThomasKrenn\ContactInfo($this->container);
            $contactInfo->load();

            $contactInfo->setAuthKey($params['authkey']);
            $contactInfo->setEmail($params['email']);
            $contactInfo->setPerson($params['person']);
            $contactInfo->setEnabledFlag(($params['enabled'] === '1') ? true : false);

            $contactInfo->write();

            $response->setSuccess(true);
        } catch (\Exception $e) {
            $response->addException($e);
        }

        return $response;
    }

    /**
     * Calls tkalert script for testing
     * @param \NETWAYS\Common\ArrayObject $params
     * @return JsonResponse
     */
    public function actionTest(\NETWAYS\Common\ArrayObject $params)
    {
        $response = new JsonResponse();

        try {

            $validator = new ArrayObjectValidator();

            $validator->addValidatorObject(
                \NETWAYS\Common\ValidatorObject::create(
                    'run',
                    _('Run flag'),
                    \NETWAYS\Common\ValidatorObject::VALIDATE_MANDATORY
                )
            );

            $validator->validateArrayObject($params);

            if ($params['run'] == '1') {
                $contactInfo = new ContactInfo($this->container);

                $alerter = new Alert($this->container);
                $alerter->configureByContactInfo($contactInfo);
                $alerter->setType(Alert::TYPE_TEST);
                $alerter->commit();
                $response->setSuccess();
            } else {
                $response->addError(_('CGI parameter run=1 is missing to start the test'));
            }

        } catch (\Exception $e) {
            $response->addException($e);
        }

        $response->addError('OH OH something happend');
        return $response;
    }
}
