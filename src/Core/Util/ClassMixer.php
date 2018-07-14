<?php

namespace ElephantWrench\Core\Util;

use ParseError;
use ReflectionClass;
use ReflectionFunctionAbstract;

final class ClassMixer
{
    private function __construct() {}

    /**
     * Dumps the declaration (source code) of a Closure.
     *
     * @param Closure $closure The closure
     * @return string
     */
    public static function dumpReflectionFunctionAsClosure(ReflectionFunctionAbstract $reflection_function)
    {
        $closure_definition = 'function (';
        $closure_definition .= implode(', ', self::getReflectionFunctionParametersAsStrings($reflection_function));
        $closure_definition .= ')' . PHP_EOL;

        $function_definition = '';
        $lines = file($reflection_function->getFileName());
        for ($line_number = $reflection_function->getStartLine(); $line_number <= $reflection_function->getEndLine(); ++$line_number)
        {
            $function_definition .= $lines[$line_number - 1];
        }

        if (preg_match("/function\s*&?\s*{$reflection_function->name}\s*\(.+?{(?P<function_body>.*)}/s",
                       $function_definition,
                       $matches))
        {
            $closure_definition .= ' {' . $matches['function_body'] . ' };';
        }
        else
        {
            // TODO: Error
        }


        return $closure_definition;
    }

    private static function getReflectionFunctionParametersAsStrings(ReflectionFunctionAbstract $reflection_function)
    {
        $function_parameter_strings = array();
        foreach ($reflection_function->getParameters() as $function_parameters)
        {
            $parameter_string = '';
            if ($function_parameters->isArray())
            {
                $parameter_string .= 'array ';
            }
            else if ($function_parameters->getClass())
            {
                $parameter_string .= $function_parameters->getClass()->name . ' ';
            }
            if ($function_parameters->isPassedByReference())
            {
                $parameter_string .= '&';
            }
            $parameter_string .= '$' . $function_parameters->name;
            if ($function_parameters->isOptional())
            {
                $parameter_string .= ' = ' . var_export($function_parameters->getDefaultValue(), True);
            }
            $function_parameter_strings[] = $parameter_string;
        }
        return $function_parameter_strings;
    }


    public static function classMethodToRealClosure($class, $method) {
        $reflection_class = new ReflectionClass($class);
        $reflection_method = $reflection_class->getMethod($method);
        try {
            eval('$closure = ' . self::dumpReflectionFunctionAsClosure($reflection_method));
        } catch(ParseError $e) {
            print PHP_EOL . PHP_EOL . self::dumpReflectionFunctionAsClosure($reflection_method) . PHP_EOL;

        }
        return $closure;
    }
}
