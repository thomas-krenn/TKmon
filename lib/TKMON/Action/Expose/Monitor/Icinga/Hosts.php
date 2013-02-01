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
class Hosts extends \TKMON\Action\Base
{
    /**
     * Show main display
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\TwigTemplate
     */
    public function actionEdit(\NETWAYS\Common\ArrayObject $params)
    {
        $template = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
        $template->setTemplateName('views/Monitor/Icinga/Hosts/List.twig');

        /** @var $hostData \TKMON\Model\Icinga\HostData */
        $hostData = $this->container['hostData'];
        $hostData->load();

        $template['host_attributes'] = $hostData->getEditableAttributes();
        $template['host_customvars'] = $hostData->getCustomVariables();
        $template['hosts'] = $hostData;

        return $template;
    }

    /**
     * Ajax data end point
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\JsonResponse
     */
    public function actionData(\NETWAYS\Common\ArrayObject $params)
    {
        $response = new \TKMON\Mvc\Output\JsonResponse();

        try {

            $validator = new \NETWAYS\Common\ArrayObjectValidator();

            $validator->addValidatorObject(
                \NETWAYS\Common\ValidatorObject::create(
                    'host_name',
                    'Host ID',
                    \NETWAYS\Common\ValidatorObject::VALIDATE_MANDATORY
                )
            );

            $validator->validateArrayObject($params);

            /** @var $hostData \TKMON\Model\Icinga\HostData */
            $hostData = $this->container['hostData'];
            $hostData->load();

            $response->addData($hostData->getHost($params['host_name'])->createDataVoyager(true));
            $response->setSuccess(true);
        } catch (\Exception $e) {
            $response->addException($e);
        }

        return $response;
    }

    /**
     * Ajax write endpoint
     *
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\JsonResponse
     * @throws \TKMON\Exception\ModelException
     */
    public function actionWrite(\NETWAYS\Common\ArrayObject $params)
    {
        $response = new \TKMON\Mvc\Output\JsonResponse();

        try {
            /** @var $hostData \TKMON\Model\Icinga\HostData */
            $hostData = $this->container['hostData'];
            $hostData->load();

            $validator = $hostData->createValidator();

            $validator->addValidatorObject(
                \NETWAYS\Common\ValidatorObject::create(
                    'action',
                    'internal action: create|edit',
                    FILTER_VALIDATE_REGEXP,
                    null,
                    array(
                        'regexp' => '/^(create|edit)$/'
                    )
                )
            );

            $validator->validateArrayObject($params);

            $action = $params['action'];
            $params->offsetUnset('action');

            /** @var $host \ICINGA\Object\Host */
            $host = null;

            if ($action === 'create') {
                $host = $hostData->createHost($params);
                $hostData->setHost($host);
            } elseif ($action === 'edit') {
                $host = $hostData->getHost($params['host_name']);
                $host->fromArrayObject($params);
                $hostData->updateHost($host);
            } else {
                throw new \TKMON\Exception\ModelException('Unknown form action: '. $action);
            }

            $hostData->write();

            $response->setSuccess(true);
        } catch (\Exception $e) {
            $response->addException($e);
        }

        return $response;
    }

    /**
     * Ajax remove endpoint
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\JsonResponse
     */
    public function actionRemove(\NETWAYS\Common\ArrayObject $params)
    {
        $response = new \TKMON\Mvc\Output\JsonResponse();
        try {
            $validator = new \NETWAYS\Common\ArrayObjectValidator();
            $validator->addValidatorObject(
                \NETWAYS\Common\ValidatorObject::create(
                    'host_name',
                    'Host identifier',
                    \NETWAYS\Common\ValidatorObject::VALIDATE_MANDATORY
                )
            );
            $validator->validateArrayObject($params);

            $hostData = $this->container['hostData'];
            $hostData->load();

            /** @var $hostData \TKMON\Model\Icinga\HostData */
            $hostData->removeHostByName($params['host_name']);

            $hostData->write();

            $response->setSuccess(true);
        } catch (\Exception $e) {
            $response->addException($e);
        }

        return $response;
    }
}
