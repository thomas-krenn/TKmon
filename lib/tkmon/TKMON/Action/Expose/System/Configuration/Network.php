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

namespace TKMON\Action\Expose\System\Configuration;

use NETWAYS\Common\ArrayObject;
use NETWAYS\Common\ArrayObjectValidator;
use NETWAYS\Common\ValidatorObject;
use TKMON\Action\Base;
use TKMON\Exception\ModelException;
use TKMON\Model\System\DnsServers;
use TKMON\Model\System\Hostname;
use TKMON\Model\System\IpAddress;
use TKMON\Model\System\NtpConfiguration;
use TKMON\Model\System;
use TKMON\Mvc\Output\JsonResponse;
use TKMON\Mvc\Output\TwigTemplate;

/**
 * Network settings
 *
 * @package TKMON\Action
 * @author Marius Hein <marius.hein@netways.de>
 */
class Network extends Base
{
    /**
     * Primary interface
     * @var string
     */
    private $primaryInterface = null;

    /**
     * Init function called from dispatcher
     * @throws ModelException
     */
    public function init()
    {
        $this->primaryInterface = $this->container['config']['system.interface'];

        if (!$this->primaryInterface) {
            throw new ModelException('Primary interface (system.interface) not configured');
        }
    }

    /**
     * Displays the form and set some basic data
     *
     * @param ArrayObject $params
     * @return TwigTemplate
     */
    public function actionIndex(ArrayObject $params)
    {
        $template = new TwigTemplate($this->container['template']);
        $template->setTemplateName('views/System/Configuration/Network.twig');

        // Hostname / Devicename
        $hostnameModel = new Hostname($this->container);
        $hostnameModel->load();
        $template['device_name'] = $hostnameModel->getCombined();

        // DNS configuration
        $dnsModel = new DnsServers($this->container);
        $dnsModel->setInterfaceName($this->primaryInterface);
        $dnsModel->load();
        $template['dns_nameserver1'] = $dnsModel->getDnsServerItem(0);
        $template['dns_nameserver2'] = $dnsModel->getDnsServerItem(1);
        $template['dns_nameserver3'] = $dnsModel->getDnsServerItem(2);
        $template['dns_search'] = $dnsModel->getDnsSearch();


        // IP Address
        $ipModel = new IpAddress($this->container);
        $ipModel->setInterfaceName($this->primaryInterface);
        $ipModel->load();
        $template['ip_address'] = $ipModel->getIpAddress();
        $template['ip_netmask'] = $ipModel->getIpNetmask();
        $template['ip_gateway'] = $ipModel->getIpGateway();
        $template['ip_config'] = $ipModel->getConfigType();

        $ntpConfiguration = new NtpConfiguration($this->container);
        $ntpConfiguration->setMaxServers(3);
        $ntpConfiguration->load();
        $template['timeserver_1'] = $ntpConfiguration->getNtpServer(0);
        $template['timeserver_2'] = $ntpConfiguration->getNtpServer(1);
        $template['timeserver_3'] = $ntpConfiguration->getNtpServer(2);

        return $template;
    }

    /**
     * Updater to change the dns configuration
     *
     * @param ArrayObject $params
     * @return JsonResponse
     * @throws ModelException
     */
    public function actionChangeDeviceName(ArrayObject $params)
    {
        $response = new JsonResponse();

        try {
            if ($params->get('device_name')) {
                $hostnameModel = new Hostname($this->container);
                $hostnameModel->load();
                $hostnameModel->setCombined($params->get('device_name'));
                $hostnameModel->write();

                $response->setSuccess(true);
            } else {
                throw new ModelException("Parameter was not sent: device_name");
            }

        } catch (\Exception $e) {
            $response->setSuccess(false);
            $response->addException($e);
        }

        return $response;
    }

    /**
     * Action to change DNS settings
     *
     * @param ArrayObject $params
     * @return JsonResponse
     */
    public function actionChangeDnsSettings(ArrayObject $params)
    {
        $response = new JsonResponse();

        try {
            $validator = new ArrayObjectValidator();
            $validator->throwOnErrors(true);

            $validator->addValidatorObject(
                ValidatorObject::create(
                    'dns_nameserver1',
                    'IP address',
                    FILTER_VALIDATE_IP
                )
            );

            $validator->addValidatorObject(
                ValidatorObject::create(
                    'dns_nameserver2',
                    'IP address',
                    FILTER_VALIDATE_IP,
                    null, // Flags
                    array(), // Options
                    false // NOT MANDATORY
                )
            );

            $validator->addValidatorObject(
                ValidatorObject::create(
                    'dns_nameserver3',
                    'IP address',
                    FILTER_VALIDATE_IP,
                    null, // Flags
                    array(), // Options
                    false // NOT MANDATORY
                )
            );

            $validator->addValidatorObject(
                ValidatorObject::create('dns_search', 'DNS suffix', ValidatorObject::VALIDATE_ANYTHING)
            );

            $validator->validateArrayObject($params);

            $systemModel = new System($this->container);

            $dnsModel = new DnsServers($this->container);
            $dnsModel->setInterfaceName($this->primaryInterface);
            $dnsModel->load();

            $dnsModel->purgeDnsServers();

            if ($params->get('dns_nameserver1')) {
                $dnsModel->setDnsServerItem(0, $params->get('dns_nameserver1'));
            }

            if ($params->get('dns_nameserver2')) {
                $dnsModel->setDnsServerItem(1, $params->get('dns_nameserver2'));
            }

            if ($params->get('dns_nameserver3')) {
                $dnsModel->setDnsServerItem(2, $params->get('dns_nameserver3'));
            }

            $dnsModel->setDnsSearch($params->get('dns_search'));

            $dnsModel->write();

            $systemModel->restartNetworkInterfaces();

            $response->setSuccess(true);


        } catch (\Exception $e) {
            $response->setSuccess(false);
            $response->addException($e);
        }

        return $response;
    }

    /**
     * Write ip address settings to file
     * @param ArrayObject $params
     * @return JsonResponse
     */
    public function actionChangeIpSettings(ArrayObject $params)
    {
        $response = new JsonResponse();
        try {

            $ipConfig = $params['ip_config'];

            $validator = new ArrayObjectValidator();

            if ($ipConfig == IpAddress::TYPE_STATIC) {

                $validator->addValidatorObject(
                    ValidatorObject::create(
                        'ip_address',
                        'IP',
                        FILTER_VALIDATE_IP
                    )
                );

                $validator->addValidatorObject(
                    ValidatorObject::create(
                        'ip_netmask',
                        'IP',
                        FILTER_VALIDATE_IP
                    )
                );

                $validator->addValidatorObject(
                    ValidatorObject::create(
                        'ip_gateway',
                        'IP',
                        FILTER_VALIDATE_IP
                    )
                );
            }

            $validator->validateArrayObject($params);

            $ipModel = new IpAddress($this->container);
            $ipModel->setInterfaceName($this->primaryInterface);
            $ipModel->load();

            $ipModel->setConfigType($ipConfig);
            
            if ($ipConfig == IpAddress::TYPE_STATIC) {
                $ipModel->setIpAddress($params['ip_address']);
                $ipModel->setIpGateway($params['ip_gateway']);
                $ipModel->setIpNetmask($params['ip_netmask']);
            }

            $ipModel->write();

            // Down and up again network interface
            $systemModel = new System($this->container);
            $systemModel->restartNetworkInterfaces();

            if ($ipConfig == IpAddress::TYPE_STATIC) {
                // Because dhclient is running and change ip
                // address again after lease time
                $systemModel->killDhcpClient();
            }

            $response->setSuccess();

        } catch (\Exception $e) {
            $response->addException($e);
        }

        return $response;
    }

    /**
     * Action to change ntp server configuration
     * @param ArrayObject $params
     * @return JsonResponse
     */
    public function actionChangeTimeSettings(ArrayObject $params)
    {
        $response = new JsonResponse();
        try {

            // Validation not needed, sanitizing already done!

            $serversFields = array('timeserver_1', 'timeserver_2', 'timeserver_3');

            $ntpConfiguration = new NtpConfiguration($this->container);
            $ntpConfiguration->setMaxServers(count($serversFields));
            $ntpConfiguration->load();
            $ntpConfiguration->purgeServers();

            foreach ($serversFields as $index => $server) {
                if ($params->get($server)) {
                    $ntpConfiguration->addNtpServer($params->get($server), $index);
                }
            }

            $ntpConfiguration->write();

            // Restart NTP daemon
            $systemModel = new System($this->container);
            $systemModel->restartNtpDaemon();

            $response->setSuccess(true);
        } catch (\Exception $e) {
            $response->addException($e);
            $response->setSuccess(false);
        }

        return $response;
    }
}
