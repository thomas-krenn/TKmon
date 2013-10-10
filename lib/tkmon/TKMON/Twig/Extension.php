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

namespace TKMON\Twig;

/**
 * TKMON twig extension. Implement twig tweaks for our software
 * @package TKMON\Twig
 * @author Marius Hein <marius.hein@netways.de>
 */
class Extension implements \Twig_ExtensionInterface
{

    /**
     * DI container
     * @var \Pimple
     */
    private $container;

    /**
     * Creates a new object
     * @param \Pimple $container
     */
    public function __construct(\Pimple $container)
    {
        $this->container = $container;
    }

    /**
     * Interface method, not used
     * @param \Twig_Environment $environment
     */
    public function initRuntime(\Twig_Environment $environment)
    {

    }

    /**
     * Interface method, not used
     * @return array
     */
    public function getTokenParsers()
    {
        return array();
    }

    /**
     * Interface method, not used
     * @return array
     */
    public function getNodeVisitors()
    {
        return array();
    }

    /**
     * Interface method, not used
     * @return array
     */
    public function getFilters()
    {
        return array();
    }

    /**
     * Interface method, not used
     * @return array
     */
    public function getTests()
    {
        return array();
    }

    /**
     * Interface method, not used
     * @return array
     */
    public function getOperators()
    {
        return array();
    }

    /**
     * Static template markers, e.g. {{ app_name }}
     * @return array
     */
    public function getGlobals()
    {
        return array(
            'app_name' => $this->container['config']->get('app.name'),
            'web_path' => $this->container['config']->get('web.path'),
            'img_path' => $this->container['config']->get('web.img_path')
        );
    }

    /**
     * Description of the extension
     * @return string
     */
    public function getName()
    {
        return 'TKMON MVC Twig extension';
    }

    /**
     * A set of functions used in this Twig environment
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'web_link' => new \Twig_Function_Method($this, 'getCurrentUrl')
        );
    }

    /**
     * Return a href/link based on argument
     *
     * Separator is '/'. Last part of argument is method
     * to call, e.g.:
     *
     * - Action: Same class but different action names 'Action'
     * - Path/To/Class/Action: Another class with action name 'Action'
     *
     * @param string $args
     * @return string
     */
    public function getCurrentUrl($args)
    {
        $num = func_num_args();
        $params = $this->container['params'];
        $uri = $params->getParameter('REQUEST_URI', null, 'header');
        $script = $this->container['config']['web.script'];

        if ($num === 0) {
            return $uri;
        } else {
            $new = explode('/', $args);

            // Url become unreadable if empty fragments at begin
            while (!$new[0]) {
                array_shift($new);
            }

            if (count($new) === 1) {
                $parts = explode('/', $uri);
                array_pop($parts);
                $parts[] = ucfirst($new[0]);
                return implode('/', $parts);
            } else {
                return $script . implode('/', $new);
            }
        }
    }
}
