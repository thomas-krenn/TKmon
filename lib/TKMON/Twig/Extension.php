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

namespace TKMON\Twig;

/**
 * TKMON twig extension. Implement twig tweaks for our software
 *
 *
 */
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
