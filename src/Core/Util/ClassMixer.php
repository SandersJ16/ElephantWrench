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
    public static function dumpReflectionFunctionAsClosure(ReflectionFunctionAbstract $reflection_method)
    {
        $closure_definition = 'function (';
        $method_parameter_strings = array();
        foreach ($reflection_method->getParameters() as $method_parameters)
        {
            $parameter_string = '';
            if ($method_parameters->isArray())
            {
                $parameter_string .= 'array ';
            }
            else if ($method_parameters->getClass())
            {
                $parameter_string .= $method_parameters->getClass()->name . ' ';
            }
            if($method_parameters->isPassedByReference())
            {
                $parameter_string .= '&';
            }
            $parameter_string .= '$' . $method_parameters->name;
            if($method_parameters->isOptional())
            {
                $parameter_string .= ' = ' . var_export($method_parameters->getDefaultValue(), True);
            }
            $method_parameter_strings[] = $parameter_string;
        }

        $closure_definition .= implode(', ', $method_parameter_strings);
        $closure_definition .= ')' . PHP_EOL;
        $lines = file($reflection_method->getFileName());

        for ($line_number = $reflection_method->getStartLine(); $line_number < $reflection_method->getEndLine(); ++$line_number) {
            $line = $lines[$line_number];

            if ($line_number == $reflection_method->getEndLine() - 1)
            {
                $line = substr_replace($line, ';', -1, 0);
            }
            $closure_definition .= $line;

        }
        return $closure_definition;
    }


    public static function classMethodToRealClosure($class, $method) {
        $reflection_class = new ReflectionClass($class);
        $reflection_method = $reflection_class->getMethod($method);
        eval('$closure = ' . self::dumpReflectionFunctionAsClosure($reflection_method));
        return $closure;
    }
}
