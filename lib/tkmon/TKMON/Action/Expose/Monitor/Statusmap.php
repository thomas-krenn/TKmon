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
use NETWAYS\Common\ArrayObjectValidator;
use NETWAYS\Common\Exception\ValidatorException;
use NETWAYS\Common\ValidatorObject;
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
        $validator = new ArrayObjectValidator();

        $validator->addValidatorObject(
            ValidatorObject::create(
                'layout',
                'Layout',
                FILTER_SANITIZE_NUMBER_INT,
                null,
                null,
                false
            )
        );

        $template = new TwigTemplate($this->container['template']);
        $template->setTemplateName('views/Monitor/Icinga/Statusmap.twig');
        $template['error'] = '';

        $template['config'] = $this->container['config'];
        $template['layouts'] = array(
            2 => 'Collapsed tree',
            3 => 'Balanced tree',
            4 => 'Circular',
            5 => 'Circular (Marked Up)',
            6 => 'Circular (Balloon)',
        );
        
        $template['canvas_width'] = $params->get('canvas_width');
        if (!$template['canvas_width']) {
            $template['canvas_width'] = 800;
        }
        $template['canvas_height'] = $params->get('canvas_height');
        if (!$template['canvas_height']) {
            $template['canvas_height'] = 600;
        }

        try {
            $validator->validateArrayObject($params);
            $template['layout'] = $params->get('layout', 5);

        } catch (ValidatorException $e) {
            $template['layout'] = 5;
            $params->set('layout', $template['layout']);
            $template['error'] = $e->getMessage();
        }

        $statusmap = new StatusmapModel($this->container);
        $imageLinkParams = array();
        foreach ($statusmap->getDefaultParamKeys() as $key) {
            $param = $params->get($key);
            if (isset($param)) {
                $imageLinkParams[] = $key . '=' . urlencode($param);
            }
        }
        $template['image_querystring'] = implode('&', $imageLinkParams);
        if ($template['image_querystring']) {
            $template['image_querystring'] = '?' . $template['image_querystring'];
        }

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