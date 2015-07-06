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


namespace TKMON\Action\Expose;

use NETWAYS\Common\Config;
use NETWAYS\Intl\Gettext;
use TKMON\Action\Base;
use TKMON\Model\Documentation as DocumentationModel;
use TKMON\Mvc\Output\Json;
use \ArrayObject;
use NETWAYS\Common\ArrayObjectValidator;
use NETWAYS\Common\ValidatorObject;

/**
 * Handle context sensitive help requests
 *
 * @package TKMON\Action
 */
class Documentation extends Base
{
    /**
     * Disable login for context sensitive help
     * @return bool
     */
    public function securityContext()
    {
        return false;
    }

    /**
     * Try to load context sensitive help
     *
     * @param ArrayObject $params
     *
     * @return Json
     */
    public function actionContext(ArrayObject $params)
    {
        $validator = new ArrayObjectValidator();
        $validator->addValidatorObject(
            ValidatorObject::create(
                'identifier',
                'Page identifier',
                ValidatorObject::VALIDATE_ANYTHING,
                null,
                null,
                true
            )
        );

        $validator->validateArrayObject($params);

        /** @var Config $config */
        $config = $this->container['config'];

        /** @var Gettext $locale */
        $locale = $this->container['intl'];

        $documentation = new DocumentationModel($this->container);
        $documentation->setBasePath($config['doc.basepath']);
        $documentation->setLocale($locale->getLocale());
        $documentation->setIdentifier($params['identifier']);

        $output = new Json();

        $output['available']    = ($documentation->findFile() === null) ? false : true;
        $output['html']         = $documentation->getDocumentation();
        $output['identifier']   = $params['identifier'];

        return $output;
    }
}
