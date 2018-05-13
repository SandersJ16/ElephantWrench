<?php

namespace ElephantWrench\Core\Traits;

trait Mixable
{
    protected static $mixables = array();

    /**
     * Register a custom macro.
     *
     * @param  string $name
     * @param  object|callable  $macro
     *
     * @return void
     */
    public static function mix($name, $macro)
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
    public function __call($method, $parameters)
    {
        if (!isset(static::$mixables[$method])) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.', static::class, $method
            ));
        }

        return call_user_func_array(static::$mixables[$method], $parameters);
    }
}
