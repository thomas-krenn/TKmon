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

namespace TKMON\Action\Expose\ThomasKrenn\Alert;

/**
 * Configure Settings for ThomasKrenn Alert Generator
 *
 * @package TKMON\Action
 * @author Marius Hein <marius.hein@netways.de>
 */
class Configuration extends \TKMON\Action\Base
{
    public function actionIndex(\NETWAYS\Common\ArrayObject $params)
    {
        $template = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
        $template->setTemplateName('views/ThomasKrenn/Alert/Configuration/Index.twig');

        $contactInfo = new \TKMON\Model\ThomasKrenn\ContactInfo($this->container);
        $contactInfo->load();

        $template['authkey'] = $contactInfo->getAuthKey();
        $template['email'] = $contactInfo->getEmail();
        $template['person'] = $contactInfo->getPerson();

        return $template;
    }

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

            $validator->validateArrayObject($params);

            $contactInfo = new \TKMON\Model\ThomasKrenn\ContactInfo($this->container);
            $contactInfo->load();

            $contactInfo->setAuthKey($params['authkey']);
            $contactInfo->setEmail($params['email']);
            $contactInfo->setPerson($params['person']);

            $contactInfo->write();

            $response->setSuccess(true);
        } catch (\Exception $e) {
            $response->addException($e);
        }

        return $response;
    }
}
