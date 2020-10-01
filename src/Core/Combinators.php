<?php

namespace ElephantWrench\Core;

abstract class Combinators
{
    /**
     * Get Combinator that sums all mixed in functions
     *
     * @param  int      $starting_value The value that the sum will start at
     *
     * @return callable
     */
    public static function sum(int $starting_value = 0) : callable
    {
        return function (array $functions, array $parameters) use ($starting_value) {
            $sum = $starting_value;
            foreach ($functions as $function) {
                $sum += $function(...$parameters);
            }
            return $sum;
        };
    }

    /**
     * Get Combinator that calls all functions
     *
     * @return callable
     */
    public static function call() : callable
    {
        return function (array $functions, array $parameters) {
            foreach ($functions as $function) {
                $function(...$parameters);
            }
        };
    }

    /**
     * Get Combinator that returns all functions return values in an array
     *
     * @param  array    $starting_array
     *
     * @return callable
     */
    public static function aggregate(array $starting_array = array()) : callable
    {
        return function (array $functions, array $parameters) use ($starting_array) {
            $array = $starting_array;
            foreach ($functions as $function) {
                $array[] = $function(...$parameters);
            }
            return $array;
        };
    }

    /**
     * Get Combinator that returns if any of the functions return truthy
     *
     * @param  boolean  $call_all If all functions should be called even if one returns true (default is false)
     *
     * @return callable
     */
    public static function any(bool $call_all = false) : callable
    {
        return function (array $functions, array $parameters) use ($call_all) {
            $any = false;
            foreach ($functions as $function) {
                if ($function(...$parameters)) {
                    $any = true;
                    if (!$call_all) {
                        break;
                    }
                }
            }
            return $any;
        };
    }

    /**
     * Get Combinator that returns if all of the functions return truthy
     *
     * @param  boolean  $call_all If all functions should be called even if one returns false (default is false)
     *
     * @return callable
     */
    public static function all(bool $call_all = false) : callable
    {
        return function (array $functions, array $parameters) use ($call_all) {
            $all = !empty($functions);
            foreach ($functions as $function) {
                if (!$function(...$parameters)) {
                    $all = false;
                    if (!$call_all) {
                        break;
                    }
                }
            }
            return $all;
        };
    }

    /**
     * Get Combinator that returns if all of the functions return falsey
     *
     * @param  bool|boolean $call_all If all functions should be called even if one returns true (default is false)
     *
     * @return callable
     */
    public static function none(bool $call_all = false) : callable
    {
        return function(array $functions, array $parameters) use ($call_all) {
            $none = true;
            foreach ($functions as $function) {
                if ($function(...$parameters)) {
                    $none = false;
                    if (!$call_all) {
                        break;
                    }
                }
            }
            return $none;
        };
    }

    /**
     * Get Combinator that returns the results of the first function that does not raise an exception
     *
     * @param  string $exception_class The Exceptions that you want to ignore (default is \Exception)
     *
     * @return callable
     */
    public static function firstSuccessful($exception_class = \Exception::class) : callable
    {
        return function(array $functions, array $parameters) use ($exception_class) {
            foreach ($functions as $function) {
                try {
                    return $function(...$parameters);
                } catch (Exception $e) {
                    if ($e instanceof $exception_class) {
                        continue;
                    }
                    throw $e;
                }
            }
        };
    }

    /**
     * Get Combinator that returns the array_merge of all its functions' return values
     *
     * @param  array    $starting_array Starting array that will be merged into
     *                                  (if empty the first mixed in function's return value will be used)
     *
     * @return callable
     */
    public static function arrayMerge(?array $starting_array = null) : callable
    {
        return self::arrayFunction('array_merge', $starting_array);
    }

    /**
     * Get Combinator that returns the array_merge of all its functions' return values in reverse order
     *
     * @param  array    $starting_array Starting array that will be merged into
     *                                  (if empty the first mixed in function's return value will be used)
     *
     * @return callable
     */
    public static function reverseArrayMerge(?array $starting_array = null) : callable
    {
        return self::reverseArrayFunction('array_merge', $starting_array);
    }

    /**
     * Get Combinator that returns the array_merge_recursive of all its functions' return values
     *
     * @param  array    $starting_array Starting array that will be merged into
     *                                  (if empty the first mixed in function's return value will be used)
     *
     * @return callable
     */
    public static function arrayMergeRecursive(?array $starting_array = null) : callable
    {
        return self::arrayFunction('array_merge_recursive', $starting_array);
    }

    /**
     * Get Combinator that returns the array_merge_recursive of all its functions' return values in reverse order
     *
     * @param  array    $starting_array Starting array that will be merged into
     *                                  (if empty the first mixed in function's return value will be used)
     *
     * @return callable
     */
    public static function reverseArrayMergeRecursive(?array $starting_array = null) : callable
    {
        return self::reverseArrayFunction('array_merge_recursive', $starting_array);
    }

    /**
     * Get Combinator that returns the array_replace of all its functions' return values
     *
     * @param  array    $starting_array Starting array that will be replaced
     *                                  (if empty the first mixed in function's return value will be used)
     *
     * @return callable
     */
    public static function arrayReplace(?array $starting_array = null) : callable
    {
        return self::arrayFunction('array_replace', $starting_array);
    }

    /**
     * Get Combinator that returns the array_replace of all its functions' return values in reverse order
     *
     * @param  array    $starting_array Starting array that will be replaced
     *                                  (if empty the first mixed in function's return value will be used)
     *
     * @return callable
     */
    public static function reverseArrayReplace(?array $starting_array = null) : callable
    {
        return self::reverseArrayFunction('array_replace', $starting_array);
    }

    /**
     * Get Combinator that returns the array_replace_recursive of all its functions' return values
     *
     * @param  array    $starting_array Starting array that will be replaced
     *                                  (if empty the first mixed in function's return value will be used)
     *
     * @return callable
     */
    public static function arrayReplaceRecursive(?array $starting_array = null) : callable
    {
        return self::arrayFunction('array_replace_recursive', $starting_array);
    }

    /**
     * Get Combinator that returns the array_replace_recursive of all its functions' return values in reverse order
     *
     * @param  array    $starting_array Starting array that will be replaced
     *                                  (if empty the first mixed in function's return value will be used)
     *
     * @return callable
     */
    public static function reverseArrayReplaceRecursive(?array $starting_array = null) : callable
    {
        return self::reverseArrayFunction('array_replace_recursive', $starting_array);
    }

    /**
     * Get Combinator that returns the array_intersect of all its functions' return values
     *
     * @param  array    $starting_array Starting array that will be intersected against
     *                                  (if empty the first mixed in function's return value will be used)
     *
     * @return callable
     */
    public static function arrayIntersect(?array $starting_array = null) : callable
    {
        return self::arrayFunction('array_intersect', $starting_array);
    }

    /**
     * Get Combinator that returns the array_intersect_assoc of all its functions' return values
     *
     * @param  array    $starting_array Starting array that will be intersected against
     *                                  (if empty the first mixed in function's return value will be used)
     *
     * @return callable
     */
    public static function arrayIntersectAssoc(?array $starting_array = null) : callable
    {
        return self::arrayFunction('array_intersect_assoc', $starting_array);
    }

    /**
     * Get Combinator that returns the array_intersect_key of all its functions' return values
     *
     * @param  array    $starting_array Starting array that will be intersected against
     *                                  (if empty the first mixed in function's return value will be used)
     *
     * @return callable
     */
    public static function arrayIntersectKey(?array $starting_array = null) : callable
    {
        return self::arrayFunction('array_intersect_key', $starting_array);
    }

    /**
     * Get Combinator that returns the array_diff of all its functions' return values
     *
     * @param  array    $starting_array Starting array that will be diffed against
     *                                  (if empty the first mixed in function's return value will be used)
     *
     * @return callable
     */
    public static function arrayDiff(?array $starting_array = null) : callable
    {
        return self::arrayFunction('array_diff', $starting_array);
    }

    /**
     * Get Combinator that returns the array_diff_assoc of all its functions' return values
     *
     * @param  array    $starting_array Starting array that will be diffed against
     *                                  (if empty the first mixed in function's return value will be used)
     *
     * @return callable
     */
    public static function arrayDiffAssoc(?array $starting_array = null) : callable
    {
        return self::arrayFunction('array_diff_assoc', $starting_array);
    }

    /**
     * Get Combinator that returns the array_diff_key of all its functions' return values
     *
     * @param  array    $starting_array Starting array that will be diffed against
     *                                  (if empty the first mixed in function's return value will be used)
     *
     * @return callable
     */
    public static function arrayDiffKey(?array $starting_array = null) : callable
    {
        return self::arrayFunction('array_diff_key', $starting_array);
    }

    /**
     * Get Combinator that returns an array function called on all its functions's return values
     *
     * @param  string   $array_function An array function
     * @param  array    $starting_array Starting array that will be used in the array function
     *                                  (if empty the first mixed in function's return value will be used)
     *
     * @return callable
     */
    private static function arrayFunction($array_function, ?array $starting_array) : callable
    {
        return function (array $functions, array $parameters) use ($array_function, $starting_array) {
            $array = $starting_array ?: ($functions ? array_shift($functions)(...$parameters) : array());
            foreach ($functions as $function) {
                $array = $array_function($array, $function(...$parameters));
            }
            return $array;
        }
    }

    /**
     * Get Combinator that returns an array function called on all its functions's return values in reverse order
     *
     * @param  string   $array_function An array function
     * @param  array    $starting_array Starting array that will be used in the array function
     *                                  (if empty the first mixed in function's return value will be used)
     *
     * @return callable
     */
    private static function reverseArrayFunction($array_function, ?array $starting_array) : callable
    {
        return function (array $functions, array $parameters) use ($array_function, $starting_array) {
            $array = $starting_array ?: ($functions ? array_shift($functions)(...$parameters) : array());
            foreach ($functions as $function) {
                $array = $array_function($function(...$parameters), $array);
            }
            return $array;
        }
    }
}
