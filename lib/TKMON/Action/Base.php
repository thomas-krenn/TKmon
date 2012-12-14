<?php

namespace TKMON\Action;

abstract class Base
{

    const FLAG_SECURITY = 1;

    /**
     * @var \Pimple
     */
    protected $container;

    /**
     * @param \Pimple $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * @return \Pimple
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param string $name name of the template
     * @return \Twig_Template
     */
    public function createTemplate($name) {
        return $this->container['template']->loadTemplate($name);
    }

    /**
     * Return flags of this action
     * @return array
     */
    public function getFlags() {
        return array();
    }

    abstract public function getActions();

}
