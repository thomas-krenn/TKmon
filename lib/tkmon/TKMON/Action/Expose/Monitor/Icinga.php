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

namespace TKMON\Action\Expose\Monitor;

use TKMON\Model\Icinga\Pnp4Nagios;
use TKMON\Mvc\Output\TwigTemplate;
use TKMON\Model\Icinga\StatusData;
use TKMON\Action\Base;

/**
 * Action handle icinga views
 *
 * @package TKMON\Action
 * @author Marius Hein <marius.hein@netways.de>
 */
class Icinga extends Base
{
    /**
     * Show service status (simplified)
     * @param \NETWAYS\Common\ArrayObject $params
     * @return TwigTemplate
     */
    public function actionServices(\NETWAYS\Common\ArrayObject $params)
    {
        $config = $this->container['config'];
        /** @var $icingaModel StatusData **/
        $icingaModel = new StatusData($this->container);
        $template = new TwigTemplate($this->container['template']);
        $template->setTemplateName('views/Monitor/Icinga/ServiceStatus.twig');
        $template['data'] = $icingaModel->getServiceStatus(
            $params->get('servicestatustypes', null),
            $params->get('sort', null),
            $params->get('order', 'asc')
        );
        $template['config'] = $this->container['config'];
        $pnpModel = new Pnp4Nagios($this->container);
        $pnpModel->setAccessUrl($config->get('pnp4nagios.url'));
        $pnpModel->setPerfdataPath($config->get('pnp4nagios.perfdata'));
        $pnpModel->useProxy(true);
        $template['pnp4nagios'] = $pnpModel;
        return $template;
    }
}
