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
    /**
     * DataProvider of methods on ClassMixerTestClassMixer that return 'hello world'
     *
     * @return array
     */
    public function helloWorldMethodNameProvider() : array
    {
        return array(['returnHelloWorldWithOpenBracketOnNewLine'],
                     ['returnHelloWorldWithOpenBracketOnSameLine'],
                     ['returnHelloWorldDefinedOnOneLine'],
                     ['returnHelloWorldDefinedOnMultiLinesButStartsOnLineOne'],
                     ['returnHelloWorldWithExtraLinesBetweenMethodBodyAndDefinition']);
    }

    /**
     * Test that a function on ClassMixerTestClass returns 'hello world' after being turned into a closure using ClassMixer::classMethodToRealClosure
     *
     * @dataProvider helloWorldMethodNameProvider
     *
     * @param string $method_name
     */
    public function testReturnHelloWorldFunctions(string $method_name)
    {
        $hello_world_closure = ClassMixer::classMethodToRealClosure(ClassMixerTestClass::class, $method_name);
        $this->assertEquals('hello world', $hello_world_closure());
    }

    /**
     * Test that methods with a parameter still require a parameter after they are turned into a closure with ClassMixer::classMethodToRealClosure
     */
    public function testCreatedClosureWithSingleUnTypeHintedParameterAcceptsAllValuesAndRequiresParamter()
    {
        $method_closure = ClassMixer::classMethodToRealClosure(ClassMixerTestClass::class, 'methodWithRequiredNonTypedHintedNonDefaultValuedParameter');

        $method_closure(1);
        $method_closure('string');
        $method_closure(new StdClass());

        try {
            $method_closure();
            $this->fail('Closure of ClassMixerTestClass::methodWithRequiredNonTypedHintedNonDefaultValuedParameter did not throw an error when called without a parameter');
        } catch (ArgumentCountError $e) {
        }
    }

    /**
     * Test that methods with a type hinted parameter still enforce TypeHinting after they are turned into a closure with ClassMixer::classMethodToRealClosure
     */
    public function testCreatedClosureWithSingleTypeHintedParameterOnlyAcceptsAppropriateTypes()
    {
        $method_closure = ClassMixer::classMethodToRealClosure(ClassMixerTestClass::class, 'methodWithStringTypeHintedNonDefaultValuedParameter');

        $method_closure('string');
        $method_closure(6); //This should work becaue PHP will coerce the integer into a string

        try {
            $method_closure(new StdClass());
            $this->fail('Closure of ClassMixerTestClass::methodWithStringTypeHintedNonDefaultValuedParameter did not throw an error when passed a non string parameter');
        } catch (TypeError $e) {
        }
    }

    /**
     * Test that methods with a parameter that has a default value still keep their default value after they are turned into a closure with ClassMixer::classMethodToRealClosure
     */
    public function testCreatedClosureWithSingleDefaultParameter()
    {
        $method_closure = ClassMixer::classMethodToRealClosure(ClassMixerTestClass::class, 'methodWithStringTypeHintedDefaultValuedParameter');

        $this->assertEquals('string', $method_closure('string'));
        $this->assertEquals(11, $method_closure(11));
        $this->assertEquals('default', $method_closure('default'));
    }

    /**
     * Test that methods with a expected return type still enforce their return type after they are turned into a closure with ClassMixer::classMethodToRealClosure
     */
    public function testCreatedClosureWithReturnTypeRespectsReturnType()
    {
        $method_closure = ClassMixer::classMethodToRealClosure(ClassMixerTestClass::class, 'methodWithStringReturnTypeThatReturnsItsFirstParameter');

        $this->assertInternalType('string', $method_closure('string'));
        $this->assertInternalType('string', $method_closure(16)); //This should work becaue PHP will coerce the integer into a string

        try {
            $method_closure(new StdClass());
            $this->fail('Closure of ClassMixerTestClass::methodWithStringReturnTypeThatReturnsItsFirstParameter allowed return type of non string');
        } catch (TypeError $e) {
        }
    }
}
