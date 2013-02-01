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

namespace TKMON\Form;

/**
 * Base field
 *
 * @package TKMON\Form
 * @author Marius Hein <marius.hein@netways.de>
 */
abstract class Field
{
    private $label;

    private $name;

    private $namePrefix;

    private $validator;

    private $mandatory=true;

    /**
     * @var \Twig_Environment
     */
    private $template;

    public function __construct($name, $label, $mandatory=true)
    {
        $this->setName($name);
        $this->setLabel($label);
        $this->setMandatory($mandatory);
    }

    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;
    }

    public function getMandatory()
    {
        return $this->mandatory;
    }



    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setNamePrefix($namePrefix)
    {
        $this->namePrefix = $namePrefix;
    }

    public function getNamePrefix()
    {
        return $this->namePrefix;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param \Twig_Environment $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return \Twig_Environment
     */
    public function getTemplate()
    {
        return $this->template;
    }

    public function toString()
    {
        $template = new \TKMON\Mvc\Output\TwigTemplate($this->template);

        $template->setTemplateName($this->getTemplateName());

        $template['name'] = $this->getNamePrefix(). $this->getName();
        $template['label'] = $this->getLabel();

        try {
            return $template->toString();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Path to a rendering template
     *
     * @return string
     */
    abstract protected function getTemplateName();

    /**
     * Create a suitable validation object for this
     * @return \NETWAYS\Common\ValidatorObject
     */
    abstract public function getValidator();
}
