<?php

namespace ElephantWrench\Core\Traits;

use Closure;
use ReflectionClass;
use ReflectionProperty;
use BadMethodCallException;

trait Mixable
{
    /**
     * Used to keep track of all added mixed functions on this class and its children
     *
     * @var array
     */
    protected static $mixable_methods = array();

    /**
     * Used to keep track of all added mixed properties on this class and its children
     *
     * @var array
     */
    protected static $mixable_properties = array();

    /**
     * Register a new function to this class
     *
     * @param  string   $name  Name of the function we want this callable as
     * @param  callable $macro A lambda or closure that will be added as a new function to this class
     */
    public static function mix(string $name, callable $macro)
    {
        static::$mixable_methods[static::class][$name] = $macro;
    }

    /**
     * Register a new Property to this class
     *
     * @param  ReflectionProperty $property
     * @param  mixed              $default_value Default value for this property
     */
    protected static function mix_property(ReflectionProperty $property, $default_value = Null)
    {
        $property->default_value = $default_value;
        static::$mixable_properties[static::class][$property->getName()] = $property;
    }

    public static function getMixedPropertyClass(string $property, bool $private_property = False)
    {
        $class = static::class;
        while ($class !== False) {
            if (isset(static::$mixable_properties[$class][$property])) {
                break;
            }
            // If private_property is True set class to False to prevent checking parent classes
            $class = $private_property ? False : get_parent_class($class);
        }
        return $class;
    }

    /**
     * Register all properties and functions of another class to this one
     *
     * @param  mixed $class Fully qualified name of a class or an instance of one
     */
    public static function mixin($mixin)
    {
        $reflection_class = new ReflectionClass($mixin);
        $default_property_values = $reflection_class->getDefaultProperties();
        foreach ($reflection_class->getProperties() as $property)
        {
            if ($property->isDefault()) {
                static::mix_property($property, $default_property_values[$property->getName()]);
            }
        }
    }

    /**
     * Returns which class a method was added at or False if it hasn't been added to this class
     *
     * @param  string       $method
     *
     * @return string|False
     */
    public static function getMixedMethodClass(string $method)
    {
        $class = static::class;
        while ($class !== False) {
            if (isset(static::$mixable_methods[$class][$method])) {
                break;
            }
            $class = get_parent_class($class);
        }
        return $class;
    }

    /**
     * Returns whether this class has had a method added to it or not
     *
     * @param  string  $method
     *
     * @return boolean
     */
    public static function hasMixedMethod(string $method) : bool
    {
        return (bool) static::getMixedMethodClass($method);
    }

    /**
     * Dynamically handle calls to the class, if a function has been
     * registered to this class then this will call it.
     *
     * @param  string  $method
     * @param  array   $parameters
     *
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call(string $method, array $parameters)
    {
        $mixed_class = static::getMixedMethodClass($method);
        if (!$mixed_class) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.', static::class, $method
            ));
        }

        $callable = static::$mixable_methods[$mixed_class][$method];
        $callable = Closure::bind($callable, $this, $mixed_class);

        return call_user_func_array($callable, $parameters);
    }

    /**
     * Dynamically hande calls to properties on the class
     *
     * @param  string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        $mixed_class = static::getMixedPropertyClass($name);
        if ($mixed_class) {
            $property = static::$mixable_properties[$mixed_class][$name];
            if ($property->isPublic()) {
                return $property->default_value;
            }
        }

        trigger_error(sprintf(
            'Undefined Property: %s::%s', static::class, $name
        ));
    }
}
