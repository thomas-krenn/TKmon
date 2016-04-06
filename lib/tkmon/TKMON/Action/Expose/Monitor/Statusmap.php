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
 * @copyright 2012-2016 NETWAYS GmbH <info@netways.de>
 */

namespace TKMON\Action\Expose\Monitor;

use NETWAYS\Common\ArrayObject;
use TKMON\Action\Base;
use TKMON\Model\Icinga\Statusmap as StatusmapModel;
use TKMON\Mvc\Output\TwigTemplate;

/**
 * Action to display status map
 */
class Statusmap extends Base
{
    /**
     * HTML presentation
     *
     * @param ArrayObject $params
     * @return TwigTemplate
     */
    public function actionView(ArrayObject $params)
    {
        $template = new TwigTemplate($this->container['template']);
        $template->setTemplateName('views/Monitor/Icinga/Statusmap.twig');
        return $template;
    }

    /**
     * Provide statusmap image
     *
     * @param ArrayObject $params
     */
    public function actionImage(ArrayObject $params)
    {
        $statusmap = new StatusmapModel($this->container);
        
        foreach ($statusmap->getDefaultParamKeys() as $key) {
            $statusmap->addParam($key, $params->get($key, null));
        }
        
        $image = $statusmap->getImage();
        $info = $statusmap->getInfo();
        header('Content-type: ' . $info['content_type']);
        header('Content-length: ' . $info['size_download']);
        echo $image;
        exit(0);
    }
}