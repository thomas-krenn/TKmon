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

namespace TKMON\Form;

/**
 * Base field
 *
 * @package TKMON\Form
 * @author Marius Hein <marius.hein@netways.de>
 */
abstract class Field
{
    /**
     * Label
     * @var string
     */
    private $label;

    /**
     * Name (parameter name)
     * @var string
     */
    private $name;

    /**
     * Prefix
     *
     * Can be configured later that the name
     *
     * @var string
     */
    private $namePrefix;

    /**
     * Flag indicates that field is mandatory
     *
     * - Controls the validator
     *
     * @var bool
     */
    private $mandatory=true;

    /**
     * Template environment
     *
     * @var \Twig_Environment
     */
    private $template;

    /**
     * Value of the field
     *
     * @var string
     */
    private $value;

    /**
     * Additional description of field
     *
     * @var string
     */
    private $description;

    /**
     * Creates new field
     * @param string $name
     * @param string $label
     * @param bool $mandatory
     */
    public function __construct($name, $label, $mandatory = true)
    {
        $this->setName($name);
        $this->setLabel($label);
        $this->setMandatory($mandatory);
    }

    /**
     * Setter for mandatory
     * @param bool $mandatory
     */
    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;
    }

    /**
     * Getter for mandatory
     * @return bool
     */
    public function getMandatory()
    {
        return $this->mandatory;
    }

    /**
     * Setter for label
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Getter for label
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Setter for name
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Getter for name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter for the name prefix
     * @param $namePrefix
     */
    public function setNamePrefix($namePrefix)
    {
        $this->namePrefix = $namePrefix;
    }

    /**
     * Getter for the name prefix
     * @return string
     */
    public function getNamePrefix()
    {
        return $this->namePrefix;
    }

    /**
     * Setter for template environment
     * @param \Twig_Environment $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Getter for template environment
     * @return \Twig_Environment
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Setter for value
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Getter for value
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Setter for description
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Getter for description
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Compile field to html
     *
     * Ensures that error exception text is in the output
     *
     * @return string
     */
    public function toString()
    {
        $template = new \TKMON\Mvc\Output\TwigTemplate($this->template);

        $template->setTemplateName($this->getTemplateName());

        $template['name'] = $this->getNamePrefix(). $this->getName();
        $template['label'] = $this->getLabel();
        $template['value'] = $this->getValue();

        if ($this->getDescription()) {
            $template['description'] = $this->getDescription();
        }

        try {
            return $template->toString();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Magic function
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Path to a rendering template
     * @return string
     */
    abstract protected function getTemplateName();

    /**
     * Create a suitable validation object for this
     * @return \NETWAYS\Common\ValidatorObject
     */
    abstract public function getValidator();
}
