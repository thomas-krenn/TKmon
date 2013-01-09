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
        $hostnameModel->load();
        $template['device_name'] = $hostnameModel->getCombined();

        $dnsModel = new \TKMON\Model\System\DnsServers($this->container);
        $dnsModel->setInterfaceName($this->container['config']['system.interface']);
        $dnsModel->load();
        $template['dns_nameserver1'] = $dnsModel->getDnsServerItem(0);
        $template['dns_nameserver2'] = $dnsModel->getDnsServerItem(1);
        $template['dns_nameserver3'] = $dnsModel->getDnsServerItem(2);
        $template['dns_search'] = $dnsModel->getDnsSearch();

        return $template;
    }

    /**
     * Updater to change the hostname
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\JsonResponse
     * @throws \TKMON\Exception\ModelException
     */
    public function actionChangeDeviceName(\NETWAYS\Common\ArrayObject $params)
    {
        $response = new \TKMON\Mvc\Output\JsonResponse();

        try {
            if ($params->get('device_name')) {
                $hostnameModel = new \TKMON\Model\System\Hostname($this->container);
                $hostnameModel->load();
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

    /**
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\JsonResponse
     */
    public function actionChangeDnsSettings(\NETWAYS\Common\ArrayObject $params)
    {
        $response = new \TKMON\Mvc\Output\JsonResponse();

        try {
            $validator = new \NETWAYS\Common\ArrayObjectValidator();
            $validator->addValidator('dns_nameserver1', 'IP address', FILTER_VALIDATE_IP);
            $validator->addValidator('dns_nameserver2', 'IP address', FILTER_VALIDATE_IP);
            $validator->addValidator('dns_nameserver3', 'IP address', FILTER_VALIDATE_IP);
            $validator->addValidator('dns_search', 'Host', FILTER_VALIDATE_REGEXP, null, array(
                'regexp' => '/^\w+\.\w+/'
            ));
            $validator->validateArrayObject($params);

            $systemModel = new \TKMON\Model\System($this->container);

            $dnsModel = new \TKMON\Model\System\DnsServers($this->container);
            $dnsModel->setInterfaceName($this->container['config']['system.interface']);
            $dnsModel->load();

            if ($params->get('dns_nameserver1')) {
                $dnsModel->setDnsServerItem(0, $params->get('dns_nameserver1'));
            }

            if ($params->get('dns_nameserver2')) {
                $dnsModel->setDnsServerItem(1, $params->get('dns_nameserver2'));
            }

            if ($params->get('dns_nameserver3')) {
                $dnsModel->setDnsServerItem(1, $params->get('dns_nameserver3'));
            }

            if ($params->get('dns_search')) {
                $dnsModel->setDnsSearch($params->get('dns_search'));
            }

            $dnsModel->write();

            $systemModel->restartNetworkInterfaces();

            $response->setSuccess(true);


        } catch (\Exception $e) {
            $response->setSuccess(false);
            $response->addException($e);
        }

        return $response;
    }
}
