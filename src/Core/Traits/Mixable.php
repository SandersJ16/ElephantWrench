<?php

namespace ElephantWrench\Core\Traits;

use Closure;
use BadMethodCallException;

trait Mixable
{
    protected static $mixables = array();

    /**
     * Register a custom macro.
     *
     * @param  string $name
     * @param  callable  $macro
     *
     * @return void
     */
    public static function mix(string $name, callable $macro)
    {
        static::$mixables[static::class][$name] = $macro;
    }

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

    public static function hasMixedFunction(string $method) : boolean
    {
        return (boolean) static::getMixedClass($method);
    }

    /**
     * Dynamically handle calls to the class.
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
