<?php

namespace TKMON\Mvc\Output;

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
