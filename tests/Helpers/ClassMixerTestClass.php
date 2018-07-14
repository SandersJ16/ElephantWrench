<?php

namespace ElephantWrench\Test\Helpers;

class ClassMixerTestClass
{
    public function returnHelloWorldWithOpenBracketOnNewLine()
    {
        return 'hello world';
    }

    public function returnHelloWorldWithOpenBracketOnSameLine() {
        return 'hello world';
    }

    public function returnHelloWorldDefinedOnOneLine() { return 'hello world'; }

    public function returnHelloWorldDefinedOnMultiLinesButStartsOnLineOne() { return 'hello world';
    }

    public function returnHelloWorldWithExtraLinesBetweenMethodBodyAndDefinition()

    {

     return 'hello world';

    }

    public function methodWithRequiredNonTypedHintedNonDefaultValuedParameter($param_1) {}

    public function methodWithStringTypeHintedNonDefaultValuedParameter(string $param_1) {}

    public function methodWithStringTypeHintedDefaultValuedParameter($param_1 = 'default') {}

    public function methodWithStringReturnTypeThatReturnsItsFirstParameter($value) : string
    {
        return $value;
    }
}
