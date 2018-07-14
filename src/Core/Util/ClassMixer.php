<?php

namespace ElephantWrench\Core\Util;

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
        $function_parameter_strings = self::getReflectionFunctionParametersAsStrings($reflection_function);

        $closure_definition .= implode(', ', $function_parameter_strings);
        $closure_definition .= ')' . PHP_EOL;
        $lines = file($reflection_function->getFileName());

        if ($reflection_function->getStartLine() == $reflection_function->getEndLine())
        {
            if (preg_match("/function\s*&?\s*{$reflection_function->name}\s*\(.+?{(?P<function_body>.*)}/",
                           $lines[$reflection_function->getStartLine() - 1],
                           $matches))
            {
                $closure_definition .= ' {' . $matches['function_body'] . ' };';
            }
            else
            {
                // TODO: Error
            }

        }
        else
        {
            for ($line_number = $reflection_function->getStartLine(); $line_number < $reflection_function->getEndLine(); ++$line_number)
            {
                $line = $lines[$line_number];

                if ($line_number == $reflection_function->getStartLine() && strpos(trim($line), '{') !== 0)
                {
                    $line = '{ ' . PHP_EOL . $line;
                }
                if ($line_number == $reflection_function->getEndLine() - 1)
                {
                    $line = substr_replace($line, ';', -1, 0);
                }
                $closure_definition .= $line;

            }
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
        print PHP_EOL . PHP_EOL . self::dumpReflectionFunctionAsClosure($reflection_method) . PHP_EOL;
        try {
            eval('$closure = ' . self::dumpReflectionFunctionAsClosure($reflection_method));
        } catch(\ParseError $e) {
            print PHP_EOL . PHP_EOL . self::dumpReflectionFunctionAsClosure($reflection_method) . PHP_EOL;

        }
        return $closure;
    }
}
