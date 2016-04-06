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
 * @copyright 2012-2015 NETWAYS GmbH <info@netways.de>
 */

namespace TKMON\Extension\Host;

use ICINGA\Base\Catalogue;
use ICINGA\Object\Host;
use NETWAYS\Chain\ReflectionHandler;
use NETWAYS\Common\ArrayObject;
use NETWAYS\Intl\Exception\SimpleTranslatorException;
use TKMON\Exception\ModelException;
use TKMON\Form\Field\Password;
use TKMON\Form\Field\Text;
use TKMON\Form\Field\TextReadonly;
use TKMON\Interfaces\ApplicationModelInterface;
use TKMON\Model\ThomasKrenn\RestInterface;
use TKMON\Model\Icinga\ServiceData;

/**
 * Attributes for ThomasKrenn products
 *
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class ThomasKrennAttributes extends ReflectionHandler implements ApplicationModelInterface
{
    /**
     * CustomVariable name for serial
     * @var string
     */
    const CV_SERIAL = 'serial';

    /**
     * CustomVariable name for operating system
     * @var string
     */
    const CV_OS = 'os';

    /**
     * CustomVariable name for Thomas-Krenn wiki link
     * @var string
     */
    const CV_TK_WIKI_LINK = 'tk_wiki_link';

    /**
     * CustomVariable name for Thomas-Krenn product title
     * @var string
     */
    const CV_TK_PRODUCT_TITLE = 'tk_product_title';

    /**
     * CustomVariable name for IPMI ip
     * @var string
     */
    const CV_IPMI_IP = 'ipmi_ip';

    /**
     * CustomVariable name for IPMI user
     * @var string
     */
    const CV_IPMI_USER = 'ipmi_user';

    /**
     * CustomVariable name for IPMI password
     * @var string
     */
    const CV_IPMI_PASSWORD = 'ipmi_password';

    /**
     * CustomVariable name for SNMP community
     * @var string
     */
    const CV_SNMP_COMMUNITY = 'snmp_community';

    /**
     * SNMPv3 username
     * @var string
     */
    const CV_SNMP3_USERNAME = 'snmp3_username';

    /**
     * SNMPv3 password
     * @var string
     */
    const CV_SNMP3_PASSWORD = 'snmp3_password';

    /**
     * DI container
     * @var \Pimple
     */
    private $container;

    /**
     * Create a new object
     * @param \Pimple $container
     */
    public function __construct(\Pimple $container)
    {
        $this->setContainer($container);
    }

    /**
     * Setter for DI container
     * @param \Pimple $container
     */
    public function setContainer(\Pimple $container)
    {
        $this->container = $container;
    }

    /**
     * Getter for DI configuration
     * @return \Pimple
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Adding ThomasKrenn specific attributes to mask
     * @param ArrayObject $attributes
     */
    public function commandDefaultCustomVariables(ArrayObject $attributes)
    {
        $attributes->fromArray(
            array(
                /*
                 * Thomas-Krenn customer data
                 */
                self::CV_SERIAL => new Text(
                    self::CV_SERIAL,
                    _('Serial'),
                    false // Not mandatory (see https://devops.netways.de/issues/2496)
                ), // Mandatory for tkalert
                self::CV_OS => new Text(
                    self::CV_OS,
                    _('Operating system'),
                    false // Not mandatory (see https://devops.netways.de/issues/2496)
                ),

                /*
                 * Thomas-Krenn product data
                 */
                self::CV_TK_WIKI_LINK => new TextReadonly(
                    self::CV_TK_WIKI_LINK,
                    _('Thomas-Krenn wiki link'),
                    false
                ),
                self::CV_TK_PRODUCT_TITLE => new TextReadonly(
                    self::CV_TK_PRODUCT_TITLE,
                    _('Thomas-Krenn product title'),
                    false
                ),

                /*
                 * Additional vars for configure check plugins
                 */
                self::CV_IPMI_IP => new Text(
                    self::CV_IPMI_IP,
                    _('IPMI IP address'),
                    false
                ),
                self::CV_IPMI_USER => new Text(
                    self::CV_IPMI_USER,
                    _('IPMI user'),
                    false
                ),
                self::CV_IPMI_PASSWORD => new Password(
                    self::CV_IPMI_PASSWORD,
                    _('IPMI password'),
                    false
                ),
                self::CV_SNMP_COMMUNITY => new Text(
                    self::CV_SNMP_COMMUNITY,
                    _('SNMP community'),
                    false
                ),
                self::CV_SNMP3_USERNAME => new Text(
                    self::CV_SNMP3_USERNAME,
                    _('SNMP V3 username'),
                    false
                ),
                self::CV_SNMP3_PASSWORD => new Text(
                    self::CV_SNMP3_PASSWORD,
                    _('SNMP V3 password'),
                    false
                )
            )
        );
    }

    /**
     * Command hook before a host is created
     * @param Host $host
     */
    public function commandBeforeHostCreate(Host $host)
    {
        $this->updateHostTemplate($host);
        $this->updateHostCustomVariables($host);

        /** @var ServiceData $serviceModel */
        $serviceModel = $this->container['serviceData'];

        // Add IPMI service to host if configuration is available
        // See #2206 for more information
        if ($host->getCustomVariable(self::CV_IPMI_IP)
            && $host->getCustomVariable(self::CV_IPMI_USER)
            && $host->getCustomVariable(self::CV_IPMI_PASSWORD)
        ) {
            $service = $serviceModel->createServiceFromCatalogue('ipmi-sensors');

            // Adds notification to service
            // https://www.netways.org/issues/2487
            $attributes = $this->container['serviceCatalogue']->getAttributes('ipmi-sensors');
            if (isset($attributes->tk_notify) && $attributes->tk_notify === true) {
                if (isset($attributes->tk_notify_default) && $attributes->tk_notify_default === true) {
                    $template = $this->container['config']->get(
                        'thomaskrenn.icinga.template.service',
                        'thomas-krenn-service'
                    );

                    $service->setUse($template);
                }
            }

            $host->addService($service);
            $service = null;

            $service = $serviceModel->createServiceFromCatalogue('net-ping-ipmi');
            $host->addService($service);

            /** @var \Logger $logger */
            $logger = $this->container['logger'];
            $logger->info(
                'Host '. $host->getName(). ' has IPMI information configured. '
                . 'Add IPMI check to this host'
            );
        }
    }

    /**
     * Command hook before a host is update
     * @param Host $host
     */
    public function commandBeforeHostUpdate(Host $host)
    {
        $this->updateHostTemplate($host);
        $this->updateHostCustomVariables($host);
    }

    /**
     * Change used template to use tk service
     *
     * Dispatch function for different commands
     *
     * @param Host $host
     */
    public function updateHostTemplate(Host $host)
    {
        $host->setUse($this->container['config']['thomaskrenn.icinga.template.host']);
    }

    /**
     * Add data to host from Thomas-Krenn webservice
     * @param Host $host
     * @throws \TKMON\Exception\ModelException
     */
    public function updateHostCustomVariables(Host $host)
    {
        if (!isset($this->container['config']['thomaskrenn.alert.authkey'])) {
            return;
        }

        if (!$host->getCustomVariable(self::CV_SERIAL)) {
            return;
        }

        $authKey = $this->container['config']['thomaskrenn.alert.authkey'];
        $serial = $host->getCustomVariable(self::CV_SERIAL);

        $restInterface = new RestInterface($this->container);
        $restInterface->setAuthKey($authKey);
        $restInterface->setLangFromUserObject($this->container['user']);

        try {
            $this->container['logger']->debug('Get product info for serial ' . $serial);
            $detailObject = $restInterface->getProductDetailFromSerial($serial);
            $host->addCustomVariable(self::CV_TK_PRODUCT_TITLE, $detailObject->title);
            $host->addCustomVariable(self::CV_TK_WIKI_LINK, $detailObject->wiki_link);
        } catch (\Exception $e) {
            $host->addCustomVariable(self::CV_TK_PRODUCT_TITLE, '');
            $host->addCustomVariable(self::CV_TK_WIKI_LINK, '');
            $this->container['logger']->error(
                'Could not find products for serial '
                . $serial
                . ' (' . $e->getMessage(). ')'
            );
            throw new ModelException('Could not find any products for '. $serial. ' (error: '. $e->getMessage(). ')');
        }
    }
}
