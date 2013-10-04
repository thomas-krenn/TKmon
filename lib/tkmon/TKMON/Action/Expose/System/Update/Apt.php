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

use TKMON\Action\Base;
use NETWAYS\Common\ArrayObject;
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
        return $template;
    }
}