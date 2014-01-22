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

namespace TKMON\Action\Expose\System\Update;

use NETWAYS\Common\ArrayObjectValidator;
use NETWAYS\Common\ValidatorObject;
use TKMON\Action\Base;
use NETWAYS\Common\ArrayObject;
use TKMON\Exception\ModelException;
use TKMON\Model\System\Update\Apt as AptModel;
use TKMON\Model\User;
use TKMON\Mvc\Output\JsonResponse;
use TKMON\Mvc\Output\TwigTemplate;

/**
 * Handle apt updates and information about that
 *
 * @package TKMON\Action
 */
class Apt extends Base
{
    /**
     * Show the form
     * @param ArrayObject $params
     * @return TwigTemplate
     */
    public function actionIndex(ArrayObject $params)
    {
        $template = new TwigTemplate($this->container['template']);
        $template->setTemplateName('views/System/Update/Apt.twig');

        /** @var User $user */
        $user = $this->container['user'];
        $template['language'] = $user->getLocale();
        return $template;
    }

    /**
     * Show pending updates
     * @param ArrayObject $params
     * @return TwigTemplate
     */
    public function actionPendingUpdates(ArrayObject $params)
    {
        $model = new AptModel($this->container);
        $template = new TwigTemplate($this->container['template']);
        $template->setTemplateName('views/System/Update/Apt/EmbeddedPendingList.twig');
        try {
            $template['records'] = $model->getPendingUpdates();
        } catch (\Exception $e) {
            $template['error'] = $e->getMessage();
        }
        return $template;
    }

    /**
     * Run apt tests
     * @param ArrayObject $params
     * @return TwigTemplate Internal html snip
     */
    public function actionTestPackages(ArrayObject $params)
    {
        $model = new AptModel($this->container);
        $template = new TwigTemplate($this->container['template']);
        $template->setTemplateName('views/System/Update/Apt/EmbeddedTestOutput.twig');

        try {
            $template['result'] = $model->testPackages();
            $template['stats'] = $model->getStats();
        } catch (\Exception $e) {
            $template['error'] = $e->getMessage();
        }

        return $template;
    }

    /**
     * Action include a restart required display
     *
     * @param   ArrayObject     $params
     * @return  TwigTemplate
     */
    public function actionRestartRequired(ArrayObject $params)
    {
        $model = new AptModel($this->container);
        $template = new TwigTemplate($this->container['template']);
        $template->setTemplateName('views/System/Update/Apt/EmbeddedRestartRequired.twig');
        $template['restartRequired'] = $model->isRestartRequired();
        return $template;
    }

    /**
     * Real system upgrade
     * @param ArrayObject $params
     * @return JsonResponse
     */
    public function actionUpgrade(ArrayObject $params)
    {
        $validator = new ArrayObjectValidator();
        $validator->addValidatorObject(
            ValidatorObject::create(
                'doUpgrade',
                'Upgrade security flag',
                ValidatorObject::VALIDATE_MANDATORY
            )
        );

        $response = new JsonResponse();

        try {
            $validator->validateArrayObject($params);

            if ($params['doUpgrade'] === '1') {
                $model = new AptModel($this->container);
                $output = $model->doUpgrade();

                $response->addData($output);

                $response->setSuccess(true);
            }
        } catch (\Exception $e) {
            $response->addException($e);
        }

        return $response;
    }
}
