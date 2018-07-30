<?php

namespace ElephantWrench\Core\Traits;

use Error;
use Closure;
use ReflectionClass;
use ReflectionProperty;
use ReflectionException;
use BadMethodCallException;

use ElephantWrench\Core\Util\{ClassMixer, ContextClosure};

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
     * Used to keep track of all mixed properties on a specific instance of this class, this array
     * will only get populated with values that have been changed from their default value, otherwise
     * their default values are used (these are stored on the properties in $mixable_properties)
     *
     * @var array
     */
    protected $mixable_instance_properties = array();

    /**
     * Register a new function to this class
     *
     * @param  string   $name    Name of the function we want this callable as
     * @param  callable $macro   A lambda or closure that will be added as a new function to this class
     * @param  int      $context Context the function should be added to this class as (Public, Protected or Private),
     *                           use the constants defined in ElephantWrench\Core\Util\ContextClosure
     */
    public static function mix(string $name, callable $macro, $context = ContextClosure::PUBLIC)
    {
        $callable_context = new ContextClosure($macro, $context);
        static::$mixable_methods[static::class][$name] = $callable_context;
    }

    /**
     * Register a new Property to this class
     *
     * @param  ReflectionProperty $property
     * @param  mixed              $default_value Default value for this property
     */
    protected static function mixProperty(ReflectionProperty $property, $default_value = null)
    {
        $property->default_value = $default_value;
        static::$mixable_properties[static::class][$property->getName()] = $property;
    }

    /**
     * Return the class in this class's hierarchy that a property was added to or false if this class has no matching property
     *
     * @param  string $property The name of the property we want to check
     *
     * @return string|false
     */
    protected static function getMixedPropertyClass(string $property)
    {
        $class = static::class;
        while ($class !== false) {
            if (isset(static::$mixable_properties[$class][$property])) {
                break;
            }
            $class = get_parent_class($class);
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
        static::mixinPropertiesFromReflectionClass($reflection_class);
        static::mixinMethodsFromReflectionClass($reflection_class);
    }

    /**
     * Mix all properties on a ReflectionClass into this class
     *
     * @param  ReflectionClass $reflection_class
     */
    protected static function mixinPropertiesFromReflectionClass(ReflectionClass $reflection_class)
    {
        $default_property_values = $reflection_class->getDefaultProperties();
        foreach ($reflection_class->getProperties() as $property) {
            if ($property->isDefault()) {
                static::mixProperty($property, $default_property_values[$property->getName()]);
            }
        }
    }

    /**
     * Mix all methods on a ReflectionClass into this class
     *
     * @param  ReflectionClass $reflection_class
     */
    protected static function mixinMethodsFromReflectionClass(ReflectionClass $reflection_class)
    {
        foreach ($reflection_class->getMethods() as $method) {
            $context = $method->isPublic() ? ContextClosure::PUBLIC : ($method->isProtected() ? ContextClosure::PROTECTED : ContextClosure::PRIVATE);
            static::mix($method->getName(), ClassMixer::reflectionFunctionToRealClosure($method), $context);
        }
    }

    /**
     * Returns which class a method was added to or false if it hasn't been added to this class
     *
     * @param  string       $method
     *
     * @return string|false
     */
    public static function getMixedMethodClass(string $method)
    {
        $class = static::class;
        while ($class !== false) {
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
     * @throws \Error
     */
    public function __call(string $method, array $parameters)
    {

        //If the method exists we throw an Error as this means someone is trying to access
        //a protected or private method not added through a mixin in the wrong context
        if (method_exists($this, $method)) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $context_class = $backtrace[1]['class'] ?? '';
            throw new Error(sprintf(
                "Cannot access non-public method %s::%s() from context '%s'", static::class, $method, $context_class
            ));
        }

        $mixed_class = static::getMixedMethodClass($method);

        //If we don't have a mixed in method than throw an Error
        if (!$mixed_class) {
            throw new Error(sprintf(
                'Call to undefined method %s::%s()', static::class, $method
            ));
        }

        //If we get to this part of the function than we are dealing with a mixed in property
        $context_closure = static::$mixable_methods[$mixed_class][$method];

        //If the method is not public than check that we are calling from an appropriate
        //context for the visibility of the function, otherwise throw an Error
        if (!$context_closure->isPublic()) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $context_class = $backtrace[1]['class'] ?? '';
            if ($context_closure->isProtected()) {
                if ($context_class != $mixed_class && !is_subclass_of($context_class, $mixed_class)) {
                    throw new Error(sprintf(
                        "Call to protected method %s::%s() from context '%s'", static::class, $method, $context_class
                    ));
                }
            } else {
                if ($context_class != $mixed_class) {
                    throw new Error(sprintf(
                        "Call to private method %s::%s() from context '%s'", static::class, $method, $context_class
                    ));
                }
            }
        }

        $closure = Closure::bind($context_closure->getClosure(), $this, $mixed_class);

        return $closure(...$parameters);
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
        //If the property exists we throw an error instead of triggering a notice as this means
        //someone is trying to access a protected or private property not added through a mixin
        //in the wrong context
        if (property_exists($this, $name)) {
            throw new Error(sprintf(
                "Cannot access non-public property %s::%s", static::class, $name
            ));
        }

        $mixed_class = static::getMixedPropertyClass($name);

        //If we don't have a mixed class then we trigger an E_USER_NOTICE
        //as this is default PHP behaviour and return null
        if (!$mixed_class) {
            trigger_error(sprintf(
                'Undefined Property: %s::%s', static::class, $name
            ), E_USER_NOTICE);
            return null;
        }

        //If we get to this part of the function than we are dealing with a mixed in property
        $property = static::$mixable_properties[$mixed_class][$name];

        //If the property is not public than check that we are calling from an appropriate
        //context for the visibility of the property, otherwise throw an Error
        if (!$property->isPublic()) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $context_class = $backtrace[1]['class'] ?? '';
            if ($property->isProtected()) {
                if ($context_class != $mixed_class && !is_subclass_of($context_class, $mixed_class)) {
                    throw new Error(sprintf(
                        "Cannot access protected property %s::%s", static::class, $name
                    ));
                }
            } else {
                if ($context_class != $mixed_class) {
                    throw new Error(sprintf(
                        "Cannot access private property %s::%s", static::class, $name
                    ));
                }
            }
        }

        return $this->mixable_instance_properties[$name] ?? $property->default_value;
    }

    /**
     * Dynamically handle calls to set properties on this class,
     * properties added through mixins are handled through this
     *
     * @param string $name  Name of the property
     * @param mixed  $value Value to set the property to
     */
    public function __set(string $name, $value)
    {
        //If the property exists we throw an error instead of triggering a notice as this means
        //someone is trying to set a protected or private variable not added through a mixin
        //in the wrong context
        if (property_exists($this, $name)) {
            throw new Error(sprintf(
                "Cannot access non-public property %s::%s", static::class, $name
            ));
        }

        $mixed_class = static::getMixedPropertyClass($name);

        //If we don't have a mixed class then we set a new public
        //property on this instance as this is the default PHP behaviour
        if (!$mixed_class) {
            $this->$name = $value;
            return;
        }

        //If we get to this part of the function than we are dealing with a mixed in property
        $property = static::$mixable_properties[$mixed_class][$name];

        //If the property is not public than check that we are calling from an appropriate
        //context for the visibility of the property, otherwise throw an Error
        if (!$property->isPublic()) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $context_class = $backtrace[1]['class'] ?? '';
            if ($property->isProtected()) {
                if ($context_class != $mixed_class && !is_subclass_of($context_class, $mixed_class)) {
                    throw new Error(sprintf(
                        "Cannot access protected property %s::%s", $mixed_class, $name
                    ));
                }
            } else {
                if ($context_class != $mixed_class) {
                    throw new Error(sprintf(
                        "Cannot access private property %s::%s", $mixed_class, $name
                    ));
                }
            }
        }

        $this->mixable_instance_properties[$name] = $value;
    }
}
