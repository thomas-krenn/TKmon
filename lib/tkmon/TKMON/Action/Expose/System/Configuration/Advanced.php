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

/**
 * Action handling advanced configuration settings
 *
 * @package TKMON\Action
 * @author Marius Hein <marius.hein@netways.de>
 */
class Advanced extends \TKMON\Action\Base
{

    /**
     * Display all config settings
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\TwigTemplate
     */
    public function actionFree(\NETWAYS\Common\ArrayObject $params)
    {
        $template = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
        $template->setTemplateName('views/System/Configuration/Free.twig');

        $settings = array();

        /** @var $config \NETWAYS\Common\Config */
        $config = $this->container['config'];

        $ary = $config->getArrayCopy();

        ksort($ary);

        foreach ($ary as $name => $value) {

            if (is_scalar($value)) {

                if (preg_match('/(authkey|tkpasswd)$/', $name)) {
                    $value = '<strong>********</strong>';
                } elseif ($value === false) {
                    $value = '[false]';
                } elseif ($value === null) {
                    $value = '[null]';
                }

                $settings[$name] = $value;
            } else {
                $settings[$name] = '<pre>'. print_r($value, true). '</pre>';
            }
        }

        $template['settings'] = $settings;

        return $template;
    }
}
