<?php

namespace ElephantWrench\Test;

use StdClass;
use TypeError;
use ArgumentCountError;

use ElephantWrench\Core\Util\ClassMixer;
use ElephantWrench\Test\Helpers\ClassMixerTestClass;

/**
 * This class tests the ClassMixer Static Class
 */
class ClassMixerTest extends ElephantWrenchBaseTestCase
{
    public function hellowWorldMethodNameProvider()
    {
        return array(['returnHelloWorldWithOpenBracketOnNewLine'],
                     ['returnHelloWorldWithOpenBracketOnSameLine'],
                     ['returnHelloWorldDefinedOnOneLine'],
                     ['returnHelloWorldDefinedOnMultiLinesButStartsOnLineOne'],
                     ['returnHelloWorldWithExtraLinesBetweenMethodBodyAndDefinition']);
    }

    /**
     * @dataProvider hellowWorldMethodNameProvider
     */
    public function testReturnHelloWorldFunctions($method_name)
    {
        $hello_world_closure = ClassMixer::classMethodToRealClosure(ClassMixerTestClass::class, $method_name);
        $this->assertEquals('hello world', $hello_world_closure());
    }

    public function testCreatedClosureWithSingleUnTypeHintedParameterAcceptsAllValuesAndRequiresParamter()
    {
        $method_closure = ClassMixer::classMethodToRealClosure(ClassMixerTestClass::class, 'methodWithRequiredNonTypedHintedNonDefaultValuedParameter');

        $method_closure(1);
        $method_closure('string');
        $method_closure(new StdClass());

        try
        {
            $method_closure();
            $this->fail('Closure of ClassMixerTestClass::methodWithRequiredNonTypedHintedNonDefaultValuedParameter did not throw an error when called without a parameter');
        }
        catch (ArgumentCountError $e) {}
    }

    public function testCreatedClosureWithSingleTypeHintedParameterOnlyAcceptsAppropriateTypes()
    {
        $method_closure = ClassMixer::classMethodToRealClosure(ClassMixerTestClass::class, 'methodWithStringTypeHintedNonDefaultValuedParameter');

        $method_closure('string');
        $method_closure(11); //This should work becaue PHP will coerce the integer into a string

        try
        {
            $method_closure(new StdClass());
            $this->fail('Closure of ClassMixerTestClass::methodWithStringTypeHintedNonDefaultValuedParameter did not throw an error when passed a non string parameter');
        }
        catch (TypeError $e) {}
    }

    public function testCreatedClosureWithSingleDefaultParameter()
    {
        $method_closure = ClassMixer::classMethodToRealClosure(ClassMixerTestClass::class, 'methodWithStringTypeHintedDefaultValuedParameter');

        $method_closure('string');
        $method_closure(11);
        $method_closure();
    }

    public function testCreatedClosureWithReturnTypeRespectsReturnType()
    {
        $method_closure = ClassMixer::classMethodToRealClosure(ClassMixerTestClass::class, 'methodWithStringReturnTypeThatReturnsItsFirstParameter');

        $method_closure('string');
        $method_closure(16); //This should work becaue PHP will coerce the integer into a string

        try
        {
            $method_closure(new StdClass());
            $this->fail('Closure of ClassMixerTestClass::methodWithStringReturnTypeThatReturnsItsFirstParameter allowed return type of non string');
        }
        catch (TypeError $e) {}

    }
}
