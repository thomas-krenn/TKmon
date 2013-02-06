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
class Services extends \TKMON\Action\Base
{
    public function actionEdit(\NETWAYS\Common\ArrayObject $params)
    {
        $template = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
        $template->setTemplateName('views/Monitor/Icinga/Services/List.twig');

        $template['hostName'] = $params->get('hostName');

        return $template;
    }

    public function actionEmbeddedList(\NETWAYS\Common\ArrayObject $params)
    {
        $response = new \TKMON\Mvc\Output\JsonResponse();

        try {

            $validator = new \NETWAYS\Common\ArrayObjectValidator();
            $validator->addValidatorObject(
                \NETWAYS\Common\ValidatorObject::create(
                    'hostName',
                    'Hostname',
                    \NETWAYS\Common\ValidatorObject::VALIDATE_MANDATORY
                )
            );

            $validator->validateArrayObject($params);

            $template = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
            $template->setTemplateName('views/Monitor/Icinga/Services/EmbeddedList.twig');

            /** @var $hostData \TKMON\Model\Icinga\HostData */
            $hostData = $this->container['hostData'];
            $hostData->load();

            $host = $hostData->getHost($params['hostName']);
            $services = $host->getServices();

            $template['services'] = $services;
            $template['host'] = $host;

            $response->addData($template->toString());

            $response->setSuccess();
        } catch (\Exception $e) {
            $response->addException($e);
        }

        return $response;
    }

    public function actionEmbeddedCreate(\NETWAYS\Common\ArrayObject $params)
    {
        $response = new \TKMON\Mvc\Output\JsonResponse();

        try {

            $validator = new \NETWAYS\Common\ArrayObjectValidator();
            $validator->addValidatorObject(
                \NETWAYS\Common\ValidatorObject::create(
                    'serviceCatalogueId',
                    'serviceCatalogueId',
                    \NETWAYS\Common\ValidatorObject::VALIDATE_MANDATORY
                )
            );

            $validator->addValidatorObject(
                \NETWAYS\Common\ValidatorObject::create(
                    'hostName',
                    'hostName',
                    \NETWAYS\Common\ValidatorObject::VALIDATE_MANDATORY
                )
            );

            $validator->validateArrayObject($params);

            $template = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
            $template->setTemplateName('views/Monitor/Icinga/Services/EmbeddedCreate.twig');

            /** @var $hostData \TKMON\Model\Icinga\HostData */
            $hostData = $this->container['hostData'];
            $hostData->load();

            $host = $hostData->getHost($params['hostName']);

            /** @var $serviceCatalogue \ICINGA\Catalogue\Services */
            $serviceCatalogue = $this->container['serviceCatalogue'];

            $item = $serviceCatalogue->getItem($params['serviceCatalogueId']);

            $check = $host->getService($item->serviceDescription);

            if ($check instanceof \ICINGA\Object\Service) {
                throw new \TKMON\Exception\ModelException(_('Service already exists on host: '. $check->serviceDescription));
            }

            $template['service'] = $item;
            $template['host'] = $host;

            $response->addData($template->toString());

            $response->setSuccess();
        } catch (\Exception $e) {
            $response->addException($e);
        }

        return $response;
    }

    public function actionCatalogueSearch(\NETWAYS\Common\ArrayObject $params)
    {
        $response = new \TKMON\Mvc\Output\JsonResponse();

        try {
            $validator = new \NETWAYS\Common\ArrayObjectValidator();

            $validator->addValidatorObject(
                \NETWAYS\Common\ValidatorObject::create(
                    'q',
                    'Query',
                    \NETWAYS\Common\ValidatorObject::VALIDATE_MANDATORY
                )
            );

            $validator->validateArrayObject($params);

            /** @var $serviceCatalogue \ICINGA\Catalogue\Services */
            $serviceCatalogue = $this->container['serviceCatalogue'];
            $result = $serviceCatalogue->query($params['q']);

            $response->setData($result->getArrayCopy());
            $response->setSuccess(true);
        } catch (\Exception $e) {
            $response->addException($e);
        }

        return $response;
    }

    public function actionRemove(\NETWAYS\Common\ArrayObject $params)
    {
        $response = new \TKMON\Mvc\Output\JsonResponse();

        try {

            $validator = new \NETWAYS\Common\ArrayObjectValidator();

            $validator->addValidatorObject(
                \NETWAYS\Common\ValidatorObject::create(
                    'hostName',
                    'Hostname',
                    \NETWAYS\Common\ValidatorObject::VALIDATE_MANDATORY
                )
            );

            $validator->addValidatorObject(
                \NETWAYS\Common\ValidatorObject::create(
                    'serviceId',
                    'ID of service',
                    \NETWAYS\Common\ValidatorObject::VALIDATE_MANDATORY
                )
            );

            $validator->validateArrayObject($params);

            /** @var $hostData \TKMON\Model\Icinga\HostData */
            $hostData = $this->container['hostData'];
            $hostData->load();

            $host = $hostData->getHost($params['hostName']);

            $service = $host->getService($params['serviceId']);
            $host->removeService($service);

            $hostData->updateHost($host);

            $hostData->write();

            $response->setSuccess(true);

        } catch (\Exception $e) {
            $response->addException($e);
        }

        return $response;
    }
}
