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

namespace ICINGA\Base;

/**
 * Object class
 *
 * @package ICINGA
 * @author Marius Hein <marius.hein@netways.de>
 *
 * @property bool register Template or active object
 * @method bool getRegister()
 * @method void setRegister(bool $value)
 *
 * @property string use Template or active object
 * @method string getUse()
 * @method void setUse(\string $value)
 *
 * @property string name Template or active object
 * @method string getName()
 * @method void setName(string $value)
 */
abstract class Object extends \NETWAYS\Common\ArrayObject
{

    const CUSTOM_VARIABLE_PREFIX = 'cf_';

    /**
     * Removed all custom variables
     *
     * And return an array
     *
     * @param \NETWAYS\Common\ArrayObject $attributes
     * @return array
     */
    private static function extractCustomVariables(\NETWAYS\Common\ArrayObject $attributes)
    {
        $out = array();
        $unset = array();
        $match = array();

        foreach ($attributes as $key => $val) {
            if (preg_match('/^'. self::CUSTOM_VARIABLE_PREFIX. '(.+)$/', $key, $match)) {
                $out[$match[1]] = $val;
                $unset[] = $key;
            }
        }

        /*
         * Extra step: Modifying the iterator while iterating throws
         * notice
         */
        foreach ($unset as $unsetVal) {
            $attributes->offsetUnset($unsetVal);
        }

        return $out;
    }

    /**
     * Create a object from attributes
     *
     * @param \NETWAYS\Common\ArrayObject $attributes
     * @return \ICINGA\Base\Object
     */
    public static function createObjectFromArray(\NETWAYS\Common\ArrayObject $attributes)
    {
        $class = get_called_class();

        $object = new $class();

        if ($object instanceof Object) {
            $customVariables = self::extractCustomVariables($attributes);
            $object->fromArrayObject($attributes);
            $object->addCustomVariables($customVariables);
        }

        return $object;
    }

    /**
     * Creates from an unstructured data voyager
     *
     * @param \stdClass $object
     * @return \NETWAYS\Common\ArrayObject
     */
    public static function createFromDataVoyager(\stdClass $object)
    {
        $data = new \NETWAYS\Common\ArrayObject();
        $data->fromVoyagerObject($object);
        return self::createObjectFromArray($data);
    }

    /**
     * Normalize object id's
     * @param string $name
     * @return string
     */
    protected static function normalizeIdentifierName($name)
    {
        return strtolower(str_replace(' ', '_', $name));
    }

    /**
     * Name ob the object
     * @var string
     */
    protected $objectName;

    /**
     * Attributes what the object can handle
     * @var array
     */
    protected $attributes = array(
        'register',
        'use',
        'name'
    );

    /**
     * Array of additional custom variables
     * @var array
     */
    protected $customVariables = array();

    /**
     * Create a new object
     *
     * Checks if a objectName was set, if not the last part
     * of class name is used
     */
    public function __construct()
    {
        if (!$this->getObjectName()) {
            $this->setObjectName($this->getObjectNameFromClass());
        }
        parent::__construct();
    }

    /**
     * Returns the last part of class
     *
     * @return string
     */
    protected function getObjectNameFromClass()
    {
        $tokens = explode('\\', get_class($this));
        return strtolower(array_pop($tokens));
    }

    /**
     * Setter for object name
     * @param string $objectName
     */
    protected function setObjectName($objectName)
    {
        $this->objectName = $objectName;
    }

    /**
     * Getter for object name
     * @return string
     */
    public function getObjectName()
    {
        return $this->objectName;
    }


    /**
     * Control method for child object
     *
     * Add additional attributes to class
     *
     * @param array $attributes
     */
    protected function addAttributes(array $attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);
    }

    /**
     * Get all attributes from class
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Normalize custom variable names
     * @param string $variableName
     * @return string
     */
    public function customVariableNameProcessor($variableName)
    {
        return strtoupper($variableName);
    }

    /**
     * Getter for custom variables
     *
     * @return array
     */
    public function getCustomVariables()
    {
        return $this->customVariables;
    }

    /**
     * Return a specific custom variable
     * @param string $name
     * @return mixed|null
     */
    public function getCustomVariable($name)
    {
        $name = $this->customVariableNameProcessor($name);
        if ($this->hasCustomVariable($name)) {
            return $this->customVariables[$name];
        }

        return null;
    }

    /**
     * Add single custom variable
     *
     * @param string $name
     * @param mixed $value
     */
    public function addCustomVariable($name, $value)
    {
        $name = $this->customVariableNameProcessor($name);
        $this->customVariables[$name] = $value;
    }

    /**
     * Add an array of variables
     *
     * @param array $variables
     */
    public function addCustomVariables(array $variables)
    {
        foreach ($variables as $name => $value) {
            $this->addCustomVariable($name, $value);
        }
    }

    /**
     * Remove single variable from object
     *
     * @param $name
     */
    public function removeCustomVariable($name)
    {
        $name = $this->customVariableNameProcessor($name);
        if ($this->hasCustomVariable($name)) {
            unset($this->customVariables[$name]);
        }
    }

    /**
     * Drop all variables
     */
    public function purgeCustomVariables()
    {
        // CAN NOT UNSET HERE
        // meta programming in effect
        $this->customVariables = array();
    }

    /**
     * Check if variables present
     *
     * @return bool
     */
    public function hasCustomVariables()
    {
        return (count($this->customVariables)>0) ? true : false;
    }

    /**
     * Checks for a specific custom variable
     *
     * @param string $name
     * @return bool
     */
    public function hasCustomVariable($name)
    {
        return array_key_exists($name, $this->customVariables);
    }

    /**
     * Assert that the attribute exists
     *
     * @param string $attribute
     * @throws \ICINGA\Exception\AttributeException
     */
    protected function assertAttributeExistence($attribute)
    {
        if (in_array($attribute, $this->attributes) === false) {
            throw new \ICINGA\Exception\AttributeException('Attribute not defined: ' . $attribute);
        }
    }

    /**
     * Add value to class
     *
     * Asserts that the attribute exists on class or
     * set internal customvars
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (strpos($offset, '_') === 0) {
            $this->addCustomVariable(substr($offset, 1), $value);
        } else {
            $this->assertAttributeExistence($offset);
            parent::offsetSet($offset, $value);
        }
    }

    /**
     * Getter for a attribute
     *
     * Asserts that the attribute exists on class
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $this->assertAttributeExistence($offset);
        if ($this->offsetExists($offset)) {
            return parent::offsetGet($offset);
        }

        return null;
    }

    /**
     * Normalize attribute names
     *
     * - testAttribute == test_attribute
     * - testFurtherAttributes == test_further_attributes
     *
     * @param string $attribute
     * @return string
     */
    protected function attributeNameProcessor($attribute)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $attribute));
    }

    /**
     * Magic caller
     *
     * - setMyAttributeName($value) sets my_attribute_name to $value
     * - getMyAttributeName() returns value of my_attribute_name
     *
     * @param string $name
     * @param array $arguments
     * @return null|string
     * @throws \ICINGA\Exception\SetException
     */
    public function __call($name, array $arguments)
    {
        $match = array();
        if (preg_match('/^(set|get)([A-Z]\w+)$/', $name, $match)) {
            $id = $match[1];
            $attribute = $this->attributeNameProcessor($match[2]);

            if ($id === 'set' && count($arguments) === 0) {
                throw new \ICINGA\Exception\SetException('Setter needs exactly one value, nothing given');
            }

            if ($id === 'set') {
                $this[$attribute] = $arguments[0];
            } elseif ($id === 'get') {
                $this->updateDependencies();
                return $this[$attribute];
            }
        }

        return null;
    }

    /**
     * Magic attribute getter
     *
     * see __call for more information
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $this->updateDependencies();
        return $this[$this->attributeNameProcessor($name)];
    }

    /**
     * Magic attribute setter
     *
     * see __call for more information
     *
     * @param string $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this[$this->attributeNameProcessor($name)] = $value;
    }

    /**
     *
     * Convert function.
     *
     * Create a icinga definition from object
     *
     * @return string
     */
    public function toString()
    {

        $this->updateDependencies(); // Make sure object data is ready

        if ($this->register !== '0') {
            $this->assertObjectIsValid(); // Check if we can use this object
        }

        $out = '';

        $out .= sprintf(
            '# Define object %s (%s)%s',
            $this->getObjectIdentifier(),
            $this->getObjectName(),
            PHP_EOL
        );

        $out .= 'define ' . $this->getObjectName() . ' {' . PHP_EOL;

        foreach ($this as $key => $value) {
            $out .= sprintf('    %-30s%s%s', $key, $value, PHP_EOL);
        }

        if ($this->hasCustomVariables()) {
            $out .= '    # Dump custom variables'. PHP_EOL;
            foreach ($this->customVariables as $name => $value) {
                $out .= sprintf('    _%-29s%s%s', $name, $value, PHP_EOL);
            }
        }

        $out .= '}';

        return $out;
    }

    /**
     * Magic conversion function
     *
     * see toString() for more information
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->toString();
        } catch (\Exception $e) {
            return '# Could not convert to string: '. $e->getMessage();
        }
    }

    /**
     * Create a unique identifier
     *
     * If you using a tuple of objects
     *
     * @return string
     */
    abstract public function getObjectIdentifier();

    /**
     * Create an identified if nothing was set
     * @throws \ICINGA\Exception\ConfigException
     */
    public function createObjectIdentifier()
    {
        throw new \ICINGA\Exception\ConfigException('Method not implemented');
    }

    /**
     * Method called to build all dependent data
     *
     * This is needed to change attributes lazy before
     * it's needed
     *
     */
    public function updateDependencies()
    {
        // PASS
        // Implementation in child objects used in parent
    }

    /**
     * Create a object
     *
     * Which can be used to transport the data to ajax services
     *
     * @param bool $withCustomVariables
     * @return \stdClass
     */
    public function createDataVoyager($withCustomVariables = false)
    {
        $obj = new \stdClass();

        $this->updateDependencies();

        foreach ($this->getAttributes() as $attr) {
            $obj->{$attr} = $this[$attr];
        }

        if ($withCustomVariables === true) {
            foreach ($this->getCustomVariables() as $key => $val) {
                $obj->{ self::CUSTOM_VARIABLE_PREFIX. strtolower($key) } = $val;
            }
        }

        return $obj;
    }

    /**
     * Test the object before writing
     *
     * @return void
     */
    abstract public function assertObjectIsValid();

    /**
     * Fill object from array object
     *
     * Loads default attributes and custom variables
     *
     * @param \ArrayObject $object
     */
    public function fromArrayObject(\ArrayObject $object)
    {
        $customVars = self::extractCustomVariables($object);

        parent::fromArrayObject($object);

        $this->addCustomVariables($customVars);
    }
}
