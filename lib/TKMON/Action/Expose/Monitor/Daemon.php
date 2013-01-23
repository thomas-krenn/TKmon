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
 * Action to control icinga daemon
 *
 * @package TKMON\Action
 * @author Marius Hein <marius.hein@netways.de>
 */
class Daemon extends \TKMON\Action\Base
{
    /**
     * Show index page and status information
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\TwigTemplate
     */
    public function actionIndex(\NETWAYS\Common\ArrayObject $params)
    {
        $template = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
        $template->setTemplateName('views/Monitor/Daemon/Status.twig');

        return $template;
    }

    /**
     * Returns a small html label if the daemon is running
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\SimpleString
     */
    public function actionStatusLabel(\NETWAYS\Common\ArrayObject $params)
    {
        $string = '<span class="label label-warning">'
            . _('Not running')
            . '</span>';

        try {
            $daemon = new \TKMON\Model\Icinga\Daemon($this->container);
            $daemon->load();

            if ($daemon->daemonIsRunning() === true) {
                $string = '<span class="label label-success">'
                    . _('Running')
                    . '</span>';
            } else {

            }
        } catch (\Exception $e) {
            // BYPASS
        }

        $output = new \TKMON\Mvc\Output\SimpleString($string);
        return $output;
    }

    public function actionConfigTest(\NETWAYS\Common\ArrayObject $params)
    {
        $daemon = new \TKMON\Model\Icinga\Daemon($this->container);

        $response = new \TKMON\Mvc\Output\JsonResponse();

        if ($daemon->testConfiguration() === true) {
            $response->setData($daemon->getConfigInfo());
            $response->setSuccess(true);
        } else {
            $response->setSuccess(false);

            foreach ($daemon->getConfigInfo() as $info) {
                $response->addError($info, \TKMON\Mvc\Output\JsonResponse::REF_TYPE_SERVER, 'icinga');
            }
        }

        return $response;
    }

    public function actionRestartIcinga(\NETWAYS\Common\ArrayObject $params)
    {
        $daemon = new \TKMON\Model\Icinga\Daemon($this->container);
        $response = new \TKMON\Mvc\Output\JsonResponse();

        try {
            $daemon->restartIcinga();
            $response->setSuccess(true);
        } catch (\Exception $e) {
            $response->addException($e);
            $response->setSuccess(false);
        }

        return $response;
    }
}
