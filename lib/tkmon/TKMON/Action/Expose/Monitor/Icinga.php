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

namespace TKMON\Action\Expose\Monitor;

/**
 * Action handle icinga views
 *
 * @package TKMON\Action
 * @author Marius Hein <marius.hein@netways.de>
 */
class Icinga extends \TKMON\Action\Base
{

    /**
     * Show hints to go to icinga interface
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\TwigTemplate
     */
    public function actionIndex(\NETWAYS\Common\ArrayObject $params)
    {
        $template = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
        $template->setTemplateName('views/Monitor/Icinga/NativeInterface.twig');

        /** @var $config \NETWAYS\Common\Config */
        $config = $this->container['config'];

        $template['icinga_href'] = $config->get('icinga.accessurl');
        $template['icinga_user'] = $config->get('icinga.adminuser');

        return $template;
    }

    /**
     * Show service status (simplified)
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\TwigTemplate
     */
    public function actionServices(\NETWAYS\Common\ArrayObject $params)
    {
        $icingaModel = new \TKMON\Model\Icinga\StatusData($this->container);
        $template = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
        $template->setTemplateName('views/Monitor/Icinga/ServiceStatus.twig');
        $template['data'] = $icingaModel->getServiceStatus($params->get('servicestatustypes', null));
        $template['config'] = $this->container['config'];
        return $template;
    }

    /**
     * Show event log
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\TwigTemplate
     */
    public function actionLogs(\NETWAYS\Common\ArrayObject $params)
    {
        $icingaModel = new \TKMON\Model\Icinga\StatusData($this->container);
        $template = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
        $template->setTemplateName('views/Monitor/Icinga/EventLog.twig');
        $template['data'] = $icingaModel->getEventLog();
        $template['config'] = $this->container['config'];
        return $template;
    }
}
