<?php

namespace TKMON\Twig;

class Extension implements \Twig_ExtensionInterface
{

    /**
     * @var \Pimple
     */
    private $container;

    public function __construct(\Pimple $container)
    {
        $this->container = $container;
    }

    public function initRuntime(\Twig_Environment $environment)
    {

    }

    public function getTokenParsers()
    {
        return array();
    }

    public function getNodeVisitors()
    {
        return array();
    }


    public function getFilters()
    {
        return array();
    }

    public function getTests()
    {
        return array();
    }

    public function getOperators()
    {
        return array();
    }


    public function getGlobals()
    {
        return array(
            'app_name' => $this->container['config']->get('app.name')
        );
    }

    public function getName()
    {
        return 'TKMON MVC Twig extension';
    }

    public function getFunctions()
    {
        return array(
            'web_link' => new \Twig_Function_Method($this, 'getCurrentUrl')
        );
    }

    public function getCurrentUrl($args)
    {
        $num = func_num_args();
        $params = $this->container['params'];
        $uri = $params->getParameter('REQUEST_URI', null, 'header');

        if ($num === 0) {
            return $uri;
        } else {
            $new = explode('/', $args);
            if (count($new) === 1) {
                $parts = explode('/', $uri);
                array_pop($parts);
                $parts[] = ucfirst($new[0]);
                return implode('/', $parts);
            } else {
                return $params->getParameter('SCRIPT_NAME', null, 'header') . '/' . implode('/', $new);
            }
        }
    }
}
