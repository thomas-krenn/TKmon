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

namespace TKMON\Action\Expose\System\Configuration;

use NETWAYS\Common\ArrayObject;
use TKMON\Action\Base;
use TKMON\Model\System;
use TKMON\Mvc\Output\TwigTemplate;
use TKMON\Mvc\Output\JsonResponse;

/**
 * Action to handle basic configuration tasks
 *
 * @package TKMON\Action
 * @author Marius Hein <marius.hein@netways.de>
 */
class Power extends Base
{
    /**
     * Show the form
     * @param ArrayObject $params
     * @return TwigTemplate
     */
    public function actionIndex(ArrayObject $params)
    {
        $template = new TwigTemplate($this->container['template']);
        $template->setTemplateName('views/System/Configuration/Power.twig');
        return $template;
    }

    /**
     * Action to handle reboot
     * @param ArrayObject $params
     * @return JsonResponse
     * @throws ModelException
     */
    public function actionApplianceReboot(ArrayObject $params)
    {
        $response = new JsonResponse();

        $systemModel = new System($this->container);

        try {
            if ($params->get('reboot', false) === '1') {
                $systemModel->doReboot();
                $response->setSuccess(true);
            } elseif ($params->get('shutdown', false) === '1') {
                $systemModel->doHalt();
                $response->setSuccess(true);
            } else {
                throw new ModelException("Not properly parametrized: Action ApplianceReboot");
            }
        } catch (\Exception $e) {
            $response->addException($e);
        }

        return $response;
    }
}
