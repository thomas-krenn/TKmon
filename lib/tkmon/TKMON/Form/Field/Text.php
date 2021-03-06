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

namespace TKMON\Form\Field;

use NETWAYS\Common\ValidatorObject;
use TKMON\Form\Field;

/**
 * Simple text field
 *
 * @package TKMON\Form
 * @author Marius Hein <marius.hein@netways.de>
 */
class Text extends Field
{
    /**
     * Template for a simple text box
     * @return string
     */
    protected function getTemplateName()
    {
        return 'fields/Text.twig';
    }

    /**
     * Creates and returns a simple string validator
     *
     * @return ValidatorObject
     */
    public function getValidator()
    {
        $validator = ValidatorObject::create(
            $this->getNamePrefix(). $this->getName(),
            $this->getLabel(),
            ValidatorObject::VALIDATE_MANDATORY
        );

        if ($this->getMandatory() === false) {
            $validator->setType(ValidatorObject::VALIDATE_ANYTHING);
            $validator->setMandatory(false);
        }

        return $validator;
    }
}
