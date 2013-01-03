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
 * Network settings
 *
 * @package TKMON\Action
 * @author Marius Hein <marius.hein@netways.de>
 */
class Network extends \TKMON\Action\Base
{
    /**
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\TwigTemplate
     */
    public function actionIndex(\NETWAYS\Common\ArrayObject $params)
    {
        $template = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
        $template->setTemplateName('views/System/Configuration/Network.twig');

        $hostnameModel = new \TKMON\Model\System\Hostname($this->container);

        $template['device_name'] = $hostnameModel->getCombined();

        return $template;
    }

    public function actionChangeDeviceName(\NETWAYS\Common\ArrayObject $params)
    {
        $response = new \TKMON\Mvc\Output\JsonResponse();

        try {
            if ($params->get('device_name')) {
                $hostnameModel = new \TKMON\Model\System\Hostname($this->container);
                $hostnameModel->setCombined($params->get('device_name'));
                $hostnameModel->write();

                $response->setSuccess(true);
            } else {
                throw new \TKMON\Exception\ModelException("Parameter was not sent: device_name");
            }

        } catch (\Exception $e) {
            $response->setSuccess(false);
            $response->addException($e);
        }

        return $response;
    }
}
