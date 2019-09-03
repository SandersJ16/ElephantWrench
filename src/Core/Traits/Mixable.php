<?php

namespace ElephantWrench\Core\Traits;

use Error;
use Closure;
use Traversable;
use ReflectionClass;
use ReflectionProperty;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionException;
use BadMethodCallException;
use InvalidArgumentException;

use ElephantWrench\Core\Exception\ClassMixerException;
use ElephantWrench\Core\Util\{ClassMixer, ContextClosure};

trait Mixable
{
    /**
     * Used to keep track of all added mixed functions on this class and its children
     *
     * @var array Nested array: $mixable_methods[Class Name][Function Name] = ContextCallable
     */
    protected static $mixable_methods = array();

    /**
     * Used to keep track of all added static mixed function on this class and its children
     *
     * @var array Nested array: $mixable_static_methods[Class Name][Function Name] = ContextCallable
     */
    protected static $mixable_static_methods = array();

    /**
     * Used to keep track of all added mixed properties on this class and its children
     *
     * @var array
     */
    protected static $mixable_properties = array();

    /**
     * Used to keep track of all added combinator functions
     *
     * @var array
     */
    protected static $combinator_methods = array();

    /**
     * Used to keep track of all mixed properties on a specific instance of this class, this array
     * will only get populated with values that have been changed from their default value, otherwise
     * their default values are used (these are stored on the properties in $mixable_properties)
     *
     * @var array
     */
    protected $mixable_instance_properties = array();

    /**
     * Returns which class a part (method or property) has been added to in an array
     * of closures by class or false if it hasn't been added to any classes in the array
     *
     * @param  string       $part
     * @param  array        $methods
     *
     * @return string|false
     */
    private static function getClassFromMixedPartArray(string $part, array $methods)
    {
        $class = static::class;
        while ($class !== false) {
            if (isset($methods[$class][$part])) {
                break;
            }
            $class = get_parent_class($class);
        }
        return $class;
    }

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
        static::$mixable_methods[static::class][$name][] = $callable_context;
    }

    public static function staticMix(string $name, callable $macro, $context = ContextClosure::PUBLIC)
    {
        $callable_context = new ContextClosure($macro, $context, true);
        static::$mixable_static_methods[static::class][$name][] = $callable_context;
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
        return self::getClassFromMixedPartArray($property, static::$mixable_properties);
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
     * @param  bool         $static
     *
     * @return string|false
     */
    public static function getMixedMethodClass(string $method, bool $static = false)
    {
        $methods = $static ? static::$mixable_static_methods : static::$mixable_methods;
        return self::getClassFromMixedPartArray($method, $methods);
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
     * Add a combinator to this class
     *
     * @param string   $name    Name of the combinator function, if one already exists with this name it'll be overriden
     * @param callable $macro   A callable that will be used as the combinator call. Must have a valid function signature
     *                          (see validateCallableFunctionSignatureForCombinator for what a valid signature is)
     */
    public static function addCombinator(string $name, callable $macro, $context = ContextClosure::PUBLIC)
    {
        self::validateCallableFunctionSignatureForCombinator($macro);

        $callable_context = new ContextClosure($macro, $context);
        static::$combinator_methods[static::class][$name] = $callable_context;
    }

    /**
     * Returns which class a combinator was added to or false if it hasn't been added to this class
     *
     * @param  string       $combinator
     *
     * @return string|false
     */
    public static function getCombinatorClass(string $combinator)
    {
        return self::getClassFromMixedPartArray($combinator, static::$combinator_methods);
    }

    /**
     * Check that a callable has a valid function signature to be a combinator. Combinator functions are called
     * with two parameters, the first parameter is an array of all the mixed in methods matching that combinators
     * name and the second paramter is an arry of parameters from that the method was called with.
     *
     * A valid function signature for a callable must:
     * - Have at least 2 parameters
     * - The first two parameters must have not type hinting or be type hinted as `array` or `Traversable`
     * - If there are more than 2 parameters than all parameters after the first two must have a default value
     * - callable may or may not specify a return type
     *
     * @param  callable $macro
     *
     * @throws InvalidArgumentException
     */
    private static function validateCallableFunctionSignatureForCombinator(callable $macro)
    {
        $base_error_message =  'Cannot register combinator to class "' . static::class . '", the callable provided was not valid.';
        $reflection_function = new ReflectionFunction($macro);
        $parameters = $reflection_function->getParameters();
        if (count($parameters) < 2) {
            throw new InvalidArgumentException($base_error_message . ' The callable must accept at least two parameters.');
        }
        foreach (array_slice($parameters, 0, 2) as $parameter) {
            $parameter_type = $parameter->getType();
            if ($parameter_type instanceof ReflectionNamedType
                && !in_array($parameter_type->getName(), array('array', Traversable::class))) {
                throw new InvalidArgumentException(sprintf(
                    '%s The The First two parameters must either be not type hinted or type hinted with "array" or "%s".'
                    . ' The parameter at position %s had type hinting of "%s"',
                    $base_error_message,
                    Traversable::class,
                    $parameter->getPosition() + 1,
                    $parameter_type->getName()
                ));
            }
        }
        foreach (array_slice($parameters, 2) as $parameter) {
            if (!$parameter->isDefaultValueAvailable()) {
                throw new InvalidArgumentException($base_error_message . ' Any parameters besides the first two must have default values.');
            }
        }
    }

    /**
     * Returns all mixed methods for a class
     *
     * @param  string       $method
     * @param  bool         $static
     *
     * @return array
     */
    public static function getMixedMethods(string $method, bool $static = false)
    {
        $mixed_methods = array();
        $all_mixed_methods = $static ? static::$mixable_static_methods : static::$mixable_methods;
        $class = static::class;
        while ($class !== false) {
            if (isset($all_mixed_methods[$class][$method])) {
                $mixed_methods = array_merge($mixed_methods, $all_mixed_methods[$class][$method]);
            }
            $class = get_parent_class($class);
        }
        return $mixed_methods;
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

        if ($mixed_class = static::getCombinatorClass($method)) {
            //If the method has a combinator than make the combinator the closure to be called and the parameters an array where
            //the first value is all the mixed methods with that name and the second value is the array of the original parameters
            $context_closure = static::$combinator_methods[$mixed_class][$method];
            $mixed_closures = array_map(function ($mixed_method) use ($mixed_class) {
                return Closure::bind($mixed_method->getClosure(), $this, $mixed_class);
            }, $this->getMixedMethods($method));

            $parameters = array($mixed_closures, $parameters);
        } elseif ($mixed_class = static::getMixedMethodClass($method)) {
            //If the method is not a combinator but has been mixed, use the last mixed in method with that name
            $context_closure = end(static::$mixable_methods[$mixed_class][$method]);
        } else {
            //If we don't have a mixed in method or combinator than throw an Error
            throw new Error(sprintf(
                'Call to undefined method %s::%s()', static::class, $method
            ));
        }

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
     * Dynamically handle static calls to the class, if a function has been
     * registered to this class as a static function then this will call it.
     *
     * @param  string  $method
     * @param  array   $parameters
     *
     * @return mixed
     *
     * @throws \Error
     */
    public static function __callStatic(string $method, array $parameters)
    {
        //If the method exists we throw an Error as this means someone is trying to access
        //a protected or private method not added through a mixin in the wrong context
        if (method_exists(static::class, $method)) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $context_class = $backtrace[1]['class'] ?? '';
            throw new Error(sprintf(
                "Cannot access non-public method %s::%s() from context '%s'", static::class, $method, $context_class
            ));
        }

        $mixed_class = static::getMixedMethodClass($method, true);

        //If we don't have a mixed in method than throw an Error
        if (!$mixed_class) {
            throw new Error(sprintf(
                'Call to undefined method %s::%s()', static::class, $method
            ));
        }

        //If we get to this part of the function than we are dealing with a mixed in static function
        $context_closure = end(static::$mixable_static_methods[$mixed_class][$method]);

        $reflection_class = new ReflectionClass(static::class);
        $fake_instance = $reflection_class->newInstanceWithoutConstructor();

        $closure = Closure::bind($context_closure->getClosure(), $fake_instance, $mixed_class);

        return $closure(...$parameters);
    }

    /**
     * Dynamically hande calls to properties on the class that are not set,
     * used to handle properties of mixed in classes
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
