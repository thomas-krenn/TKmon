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

namespace TKMON\Action\Expose\Monitor\Icinga;
use NETWAYS\Common\ValidatorObject;
use NETWAYS\Common\ArrayObjectValidator;

/**
 * Action handle contacts views
 *
 * @package TKMON\Action
 * @author Marius Hein <marius.hein@netways.de>
 */
class Contacts extends \TKMON\Action\Base
{
    /**
     * Display edit form
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\TwigTemplate
     */
    public function actionEdit(\NETWAYS\Common\ArrayObject $params)
    {

        $contacts = new \TKMON\Model\Icinga\ContactData($this->container);
        $contacts->load();
        $contacts->ksort();

        $template = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
        $template->setTemplateName('views/Monitor/Icinga/Contacts/List.twig');
        $template['contacts'] = $contacts;

        return $template;
    }

    /**
     * Ajax data action
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\JsonResponse
     */
    public function actionData(\NETWAYS\Common\ArrayObject $params)
    {
        $contacts = new \TKMON\Model\Icinga\ContactData($this->container);

        $response = new \TKMON\Mvc\Output\JsonResponse();

        try {

            $validator = new ArrayObjectValidator();

            $validator->addValidatorObject(
                \NETWAYS\Common\ValidatorObject::create(
                    'contact_name',
                    'Contact ID',
                    \NETWAYS\Common\ValidatorObject::VALIDATE_MANDATORY
                )
            );

            $validator->validateArrayObject($params);

            $contacts->load();
            $response->addData($contacts->getContact($params->get('contact_name'))->createDataVoyager(true));
            $response->setSuccess(true);

        } catch (\Exception $e) {
            $response->setSuccess(false);
            $response->addException($e);
        }

        return $response;
    }

    /**
     * Json Api write data
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\JsonResponse
     */
    public function actionWrite(\NETWAYS\Common\ArrayObject $params)
    {
        $contacts = new \TKMON\Model\Icinga\ContactData($this->container);

        $response = new \TKMON\Mvc\Output\JsonResponse();

        try {
            $contacts->load();

            $validator = new ArrayObjectValidator();

            $validator->addValidatorObject(
                ValidatorObject::create(
                    'contact_name',
                    'Mandatory',
                    ArrayObjectValidator::VALIDATE_MANDATORY
                )
            );

            $validator->addValidatorObject(
                ValidatorObject::create(
                    'alias',
                    'Mandatory',
                    ArrayObjectValidator::VALIDATE_MANDATORY
                )
            );

            $validator->addValidatorObject(
                ValidatorObject::create(
                    'email',
                    'Email',
                    FILTER_VALIDATE_EMAIL
                )
            );

            $validator->addValidatorObject(
                ValidatorObject::create(
                    'pager',
                    'pager',
                    ValidatorObject::VALIDATE_ANYTHING
                )
            );

            $validator->validateArrayObject($params);

            $record = $contacts->createContact($params);

            if ($params['contact_name'] === '###new###') {
                $record->createObjectIdentifier();
                $contacts->setContact($record);
            } else {
                $contacts->updateContact($record);
            }

            $contacts->write();

            $response->setSuccess(true);

        } catch (\Exception $e) {
            $response->addException($e);
        }

        return $response;
    }

    /**
     * Json Api, remove contact
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\JsonResponse
     */
    public function actionRemove(\NETWAYS\Common\ArrayObject $params)
    {
        $contacts = new \TKMON\Model\Icinga\ContactData($this->container);

        $response = new \TKMON\Mvc\Output\JsonResponse();

        try {
            $contacts->load();

            $validator = new ArrayObjectValidator();

            $validator->addValidator(
                'contact_name',
                'Mandatory',
                ArrayObjectValidator::VALIDATE_MANDATORY
            );

            $validator->validateArrayObject($params);

            $contacts->removeContactByName($params['contact_name']);

            $contacts->write();

            $response->setSuccess(true);

        } catch (\Exception $e) {
            $response->addException($e);
        }

        return $response;
    }
}
