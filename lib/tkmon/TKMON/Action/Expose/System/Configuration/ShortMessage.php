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
use TKMON\Exception\ModelException;
use TKMON\Model\Icinga\ContactData;
use TKMON\Model\System\ShortMessage as SmsModel;
use TKMON\Mvc\Output\JsonResponse;

/**
 * Action to controll sms alert feature
 */
class ShortMessage extends \TKMON\Action\Base
{
    /**
     * @var SmsModel
     */
    private $model;

    /**
     * @return SmsModel
     */
    private function getModel()
    {
        if ($this->model === null) {
            $this->model = new SmsModel($this->container);
        }
        return $this->model;
    }

    /**
     * @param ArrayObject $params
     * @return \TKMON\Mvc\Output\TwigTemplate
     */
    public function actionIndex(ArrayObject $params)
    {
        $template = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
        $template->setTemplateName('views/System/Configuration/ShortMessage.twig');
        $template['status'] = $this->getModel()->isEnabled();
        return $template;
    }

    /**
     * @param ArrayObject $params
     * @return \TKMON\Mvc\Output\SimpleString
     */
    public function actionSmsEnabled(ArrayObject $params)
    {
        if ($this->getModel()->isEnabled() === true) {
            return  new \TKMON\Mvc\Output\SimpleString(
                '<span class="label label-success">'. _('Enabled'). '</span>'
            );
        }

        return  new \TKMON\Mvc\Output\SimpleString(
            '<span class="label label-important">'. _('Disabled'). '</span>'
        );
    }

    /**
     * @param ArrayObject $params
     * @return JsonResponse
     */
    public function actionChangeSmsAlert(ArrayObject $params)
    {
        $model = $this->getModel();
        $val = $params->get('enable');
        $response = new JsonResponse();

        try {
            if ($val === "1") {
                $model->enable(true);
                $response->setSuccess(true);
            } elseif ($val === "0") {
                $model->enable(false);
                $response->setSuccess(true);
            } else {
                throw new ModelException(
                    "Invalid arguments, enable have to be 0/1"
                );
            }

            $contacts = new ContactData($this->container, $model->isEnabled());
            $contacts->load();
            $contacts->resetBaseRecord();
            $contacts->write();

        } catch (\Exception $e) {
            $response->setSuccess(false);
            $response->addException($e);
        }

        return $response;
    }
}
