<?php
/*
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
 */

namespace TKMON\Action\Expose;

/**
 * Index action
 *
 * @package TKMON\Action
 * @author Marius Hein <marius.hein@netways.de>
 */
class Index extends \TKMON\Action\Base
{
    public function getActions()
    {
        return array('Index');
    }

    public function actionIndex()
    {
        $user = $this->container['user'];

        $view = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);

        if ($user->getAuthenticated() === true) {
            $view->setTemplateName('views/welcome.html');
        } else {
            $view->setTemplateName('views/welcome-guest.html');
        }

        return $view;
    }
}
