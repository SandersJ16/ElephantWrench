<?php

namespace ElephantWrench\Core\Util;

use Closure;
use ParseError;
use ReflectionClass;
use ReflectionFunctionAbstract;

use ElephantWrench\Core\Exception\ClassMixerException;

/**
 * This Class is used to create Closures of methods on classes that can
 * be mixed into other classes using the ElephantWrench\Core\Traits\Mixable Trait
 *
 * It does this by getting a reflection method and
 */
final class ClassMixer
{
    /**
     * This is a Static Class so don't allow instances
     */
    private function __construct()
    {
    }

    /**
     * Given reflection function return a string that can be evaled to give you a closure equivalent to the function
     *
     * @param  ReflectionFunctionAbstract $reflection_function
     *
     * @return string
     */
    public static function dumpReflectionFunctionAsClosure(ReflectionFunctionAbstract $reflection_function) : string
    {
        $closure_definition = 'function (';
        $closure_definition .= implode(', ', self::getReflectionFunctionParametersAsStrings($reflection_function));
        $closure_definition .= ')';

        if ($reflection_function->hasReturnType()) {
            $closure_definition .= ' : ' . $reflection_function->getReturnType();
        }

        $closure_definition .= ' {' . PHP_EOL;

        $function_definition = '';
        $lines = file($reflection_function->getFileName());
        for ($line_number = $reflection_function->getStartLine(); $line_number <= $reflection_function->getEndLine(); ++$line_number) {
            $function_definition .= $lines[$line_number - 1];
        }

        if (preg_match(
            "/function\s*&?\s*{$reflection_function->name}\s*\(.+?{(?P<function_body>.*)}/s",
            $function_definition,
            $matches
        )) {
            $closure_definition .= $matches['function_body'];
        } else {
            throw new ClassMixerException("Could not parse function {$reflection_function->name} from file {$reflection_function->getFileName()}. Please make sure that multiple functions are not defined on the same line.");
        }
        $closure_definition .= '};';

        return $closure_definition;
    }

    /**
     * Get the parameters of a reflection function as strings of their definition
     *
     * @param  ReflectionFunctionAbstract $reflection_function
     *
     * @return array
     */
    private static function getReflectionFunctionParametersAsStrings(ReflectionFunctionAbstract $reflection_function) : array
    {
        $function_parameter_strings = array();
        foreach ($reflection_function->getParameters() as $function_parameter) {
            $parameter_string = '';

            if ($function_parameter->hasType()) {
                $parameter_string .= $function_parameter->getType() . ' ';
            }

            if ($function_parameter->isPassedByReference()) {
                $parameter_string .= '&';
            }
            $parameter_string .= '$' . $function_parameter->name;


            if ($function_parameter->isDefaultValueAvailable()) {
                $parameter_string .= ' = ';
                if ($function_parameter->isDefaultValueConstant()) {
                    $parameter_string .= $function_parameter->getDefaultValueConstantName();
                } else {
                    $parameter_string .= var_export($function_parameter->getDefaultValue(), true);
                }
            }
            $function_parameter_strings[] = $parameter_string;
        }
        return $function_parameter_strings;
    }

    /**
     * Given a class and a method on that class return a closure of that method
     *
     * @param  string $class
     * @param  string $method
     *
     * @return Closure
     */
    public static function classMethodToRealClosure(string $class, string $method) : Closure
    {
        $reflection_class = new ReflectionClass($class);
        $reflection_method = $reflection_class->getMethod($method);
        return self::reflectionFunctionToRealClosure($reflection_method);
    }

    /**
     * Given a reflection function return a closure of that method
     *
     * @param  ReflectionFunctionAbstract $reflection_function
     *
     * @return Closure
     */
    public static function reflectionFunctionToRealClosure(ReflectionFunctionAbstract $reflection_function) : Closure
    {
        try {
            eval('$closure = ' . self::dumpReflectionFunctionAsClosure($reflection_function));
        } catch (ParseError $e) {
            //print PHP_EOL . PHP_EOL . self::dumpReflectionFunctionAsClosure($reflection_function) . PHP_EOL;
            throw new ClassMixerException("Function {$reflection_function->getName()} was incorrectly parsed and failed to be built");
        }
        return $closure;
    }
}
