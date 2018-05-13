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
        static::$mixables[$name] = $macro;
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
        if (!isset(static::$mixables[$method])) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.', static::class, $method
            ));
        }

        $macro = static::$mixables[$method];
        if ($macro instanceof Closure) {
            $macro = $macro->bindTo($this, static::class);
        } else {
            $macro = Closure::bind($callback, $this);
        }
        return call_user_func_array($macro, $parameters);
    }
}
