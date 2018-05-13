<?php

namespace ElephantWrench\Core\Traits;

use Closure;
use BadMethodCallException;

trait Mixable
{
    /**
     * Used to keep track of all added mixed functions on this class and its children
     *
     * @var array
     */
    protected static $mixables = array();

    /**
     * Register a new function to this class
     *
     * @param  string   $name  Name of the function we want this callable as
     * @param  callable $macro A lambda or closure that will be added as a new function to this class
     */
    public static function mix(string $name, callable $macro)
    {
        static::$mixables[static::class][$name] = $macro;
    }

    /**
     * Returns which class a method was added at or False if it hasn't been added to this class
     *
     * @param  string       $method
     *
     * @return string|False
     */
    public static function getMixedClass(string $method)
    {
        $class = static::class;
        while ($class !== False) {
            if (isset(static::$mixables[$class][$method])) {
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
    public static function hasMixedFunction(string $method) : bool
    {
        return (bool) static::getMixedClass($method);
    }

    /**
     * Dynamically handle calls to the class, if a function has been
     * registered to this class then this will call it.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call(string $method, array $parameters)
    {
        $mixed_class = static::getMixedClass($method);
        if (!$mixed_class) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.', static::class, $method
            ));
        }

        $callable = static::$mixables[$mixed_class][$method];

        if ($callable instanceof Closure) {
            $callable = $callable->bindTo($this, $mixed_class);
        } else {
            $callable = Closure::bind($callable, $this, $mixed_class);
        }
        return call_user_func_array($callable, $parameters);
    }
}
