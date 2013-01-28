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

namespace TKMON\Action\Expose\Monitor\Icinga;

/**
 * Action handle contacts views
 *
 * @package TKMON\Action
 * @author Marius Hein <marius.hein@netways.de>
 */
class Contacts extends \TKMON\Action\Base
{

    public function actionEdit(\NETWAYS\Common\ArrayObject $params)
    {

        $contacts = new \TKMON\Model\Icinga\ContactData($this->container);
        $contacts->load();

        $template = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
        $template->setTemplateName('views/Monitor/Icinga/Contacts/List.twig');
        $template['contacts'] = $contacts;

        return $template;
    }

    public function actionData(\NETWAYS\Common\ArrayObject $params)
    {
        $contacts = new \TKMON\Model\Icinga\ContactData($this->container);

        $response = new \TKMON\Mvc\Output\JsonResponse();

        try {
            $contacts->load();
            $response->addData($contacts->getContact($params->get('contact_name')));
            $response->setSuccess(true);

        } catch (\Exception $e) {
            $response->setSuccess(false);
            $response->addException($e);
        }

        return $response;
    }

    public function actionWrite(\NETWAYS\Common\ArrayObject $params)
    {
        $contacts = new \TKMON\Model\Icinga\ContactData($this->container);

        $response = new \TKMON\Mvc\Output\JsonResponse();

        try {
            $contacts->load();

            $validator = new \NETWAYS\Common\ArrayObjectValidator();

            $validator->addValidator(
                'contact_name',
                'Mandatory',
                \NETWAYS\Common\ArrayObjectValidator::VALIDATE_MANDATORY
            );

            $validator->addValidator(
                'alias',
                'Mandatory',
                \NETWAYS\Common\ArrayObjectValidator::VALIDATE_MANDATORY
            );

            $validator->addValidator(
                'email',
                'Email',
                FILTER_VALIDATE_EMAIL
            );

            $validator->validateArrayObject($params);

            $response->setSuccess(true);

        } catch (\Exception $e) {
            $response->addException($e);
        }

        return $response;
    }

}
