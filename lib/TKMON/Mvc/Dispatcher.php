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

namespace TKMON\Mvc;

/**
 * Dispatcher to handle the (web) request
 * @package TKMON\Mvc
 * @author Marius Hein <marius.hein@netways.de>
 */
class Dispatcher
{

    const ACTION_PREFIX = 'action';

    /**
     * DI container
     * @var null|Pimple
     */
    private $container = null;

    /**
     * Current class
     * @var string
     */
    private $class = '';

    /**
     * Current action
     * @var string
     */
    private $action = '';

    /**
     * Current URI
     * @var string
     */
    private $uri = '';

    /**
     * Creates a new dispatcher
     * @param \Pimple $container
     */
    public function __construct(\Pimple $container)
    {
        $this->container = $container;
        $this->initializeData();
    }

    /**
     * Setter for action name
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Getter for action
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Setter for class
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * Getter for class
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Setter for the container
     * @param \Pimple $container
     */
    public function setContainer(\Pimple $container)
    {
        $this->container = $container;
    }

    /**
     * Getter for container
     * @return \Pimple
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Setter for URI
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * Getter for URI
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Method decides which URI model is currently working and
     * returns the url
     * @return string
     */
    private function determineUri()
    {
        $params = $this->container['params'];

        if ($params->hasParameter('path')) {
            return $params->getParameter('path');
        } elseif ($params->hasParameter('PATH_INFO', 'header')) {
            return $params->getParameter('PATH_INFO', null, 'header');
        }

    }

    /**
     * Fills the object with db (action, class) from URL
     */
    private function initializeData()
    {
        $this->uri = $this->determineUri();

        if ($this->uri == '/' || !$this->uri) {
            $parts = array('Index', 'Index');
        } else {
            $parts = explode('/', $this->uri);
            array_shift($parts);
        }

        $this->action = array_pop($parts);
        $this->class = $this->container['config']->get('mvc.action.namespace') . '\\' . implode('\\', $parts);
    }

    /**
     * Calls our action in class and setup template to display
     * @return string
     */
    public function dispatchRequest()
    {
        try {
            $reflectionClass = $this->getActionReflection($this->class);
            $object = $reflectionClass->newInstance();

            $object->setContainer($this->container);

            $reflectionMethod = $this->getActionMethod($object, $reflectionClass, $this->action);

            $content = $reflectionMethod->invoke($object);

            if (is_object($content) && $content instanceof \TKMON\Mvc\Output\DataInterface) {
                if ($this->isAjaxRequest()) {
                    return $content->toString();
                } else {
                    return $this->renderTemplate($content->toString());
                }
            }

            throw new \TKMON\Exception\DispatcherException('Output is not type of DataInterface');
        } catch (\TKMON\Exception\DispatcherException $e) {
            if ($this->isAjaxRequest()) {
                $response = new \TKMON\Mvc\Output\JsonResponse();
                $response->setSuccess(false);
                $response->addException($e);
                return $response->toString();
            } else {
                $response = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
                $response->setTemplateName('views/exception.twig');
                $response['exception'] = $e;
                $response['type'] = get_class($e);
                $response['text'] = (string)$e;
                return $this->renderTemplate($response->toString());
            }
        }
    }

    /**
     * Test the header if we are an ajax request or not
     * @return bool
     */
    private function isAjaxRequest()
    {
        $testAjax = $this->container['params']->getParameter('HTTP_X_REQUESTED_WITH', false, 'header');
        return ($testAjax && strtolower($testAjax) === 'xmlhttprequest')
            ? true : false;
    }

    /**
     * Renders the template
     * @param $content
     * @return string
     */
    private function renderTemplate($content)
    {
        $template = $this->container['template']->loadTemplate($this->container['config']->get('template.file'));
        return $template->render(
            array(
                'content' => $content,
                'user' => $this->container['user'],
                'config' => $this->container['config'],
                'navigation' => $this->container['navigation']
            )
        );
    }

    /**
     * Return the action method as ReflectionMethod
     * @param \TKMON\Action\Base $object
     * @param \ReflectionClass $class
     * @param string $actionName
     * @return \ReflectionMethod
     * @throws \TKMON\Exception\DispatcherException
     */
    private function getActionMethod(\TKMON\Action\Base $object, \ReflectionClass $class, $actionName)
    {
        $methodName = self::ACTION_PREFIX . $actionName;
        if ($class->hasMethod($methodName)) {
            return $class->getMethod($methodName);
        }

        throw new \TKMON\Exception\DispatcherException('Method not found: ' . $methodName);
    }

    /**
     * Returns the class as ReflectionClass object
     * @param $className
     * @throws \TKMON\Exception\DispatcherException
     * @return \ReflectionClass
     */
    private function getActionReflection($className)
    {

        if (class_exists($className)) {
            $reflection = new \ReflectionClass($className);

            if ($reflection->getParentClass()->getName() === 'TKMON\Action\Base') {
                return $reflection;
            }

            throw new \TKMON\Exception\DispatcherException('Parent class is not "TKMON\Action\Base"');
        }

        throw new \TKMON\Exception\DispatcherException('Could not load class from URI: ' . $className);
    }
}
