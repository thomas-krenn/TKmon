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

namespace TKMON\Mvc\Output;

/**
 * Create predefined twig template outputs
 * @package TKMON\Twig
 * @author Marius Hein <marius.hein@netways.de>
 */
class TwigTemplate extends \NETWAYS\Common\ArrayObject implements DataInterface
{

    /**
     * @var string
     */
    private $templateName;

    /**
     * @var \Twig_Environment
     */
    private $twigEnvironment;

    public function __construct(\Twig_Environment $twig, $templateName = null)
    {
        $this->twigEnvironment = $twig;

        if ($templateName !== null) {
            $this->templateName = $templateName;
        }
    }

    /**
     * @param string $templateName
     */
    public function setTemplateName($templateName)
    {
        $this->templateName = $templateName;
    }

    /**
     * @return string
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }

    /**
     * @param \Twig_Environment $twigEnvironment
     */
    public function setTwigEnvironment($twigEnvironment)
    {
        $this->twigEnvironment = $twigEnvironment;
    }

    /**
     * @return \Twig_Environment
     */
    public function getTwigEnvironment()
    {
        return $this->twigEnvironment;
    }

    public function getData()
    {
        return $this->getArrayCopy();
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString()
    {
        $template = $this->twigEnvironment->loadTemplate($this->getTemplateName());
        return $template->render((array)$this);
    }

}
