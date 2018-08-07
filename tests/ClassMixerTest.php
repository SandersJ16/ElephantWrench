<?php

namespace ElephantWrench\Test;

use StdClass;
use TypeError;
use ArgumentCountError;
use ReflectionClass;

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

    /**
     * Data Provider for testHasInstanceContext. Returns array of all methods on
     * ClassMixerTestClass to check instance context on and the expected results
     *
     * @return array
     */
    public function dataProviderClassMixerTestClassesThatHaveInstanceContext() : array
    {
        return array('$this calling a method' =>
                        array(true, 'methodThatUsesNonStaticFunctionCall'),
                     '$this passed to a function inside of a function' =>
                        array(true, 'methodThatPassesCurrentInstanceToAFunction'),
                     '$this as the last thing on a line' =>
                        array(true, 'methodWithThisAtEndOfALine'),
                     '$this in HereDoc' =>
                        array(true, 'methodWithThisInHereDocs'),
                     '$this in HereDoc (complex)' =>
                        array(true, 'methodWithThisInHereDocsWithBracketEscape'),
                     '$this in HereDoc with function call (complex)' =>
                        array(true, 'methodWithThisInHereDocsWithBracketEscapeAndFunctionCall'),
                     '$this in double quotes' =>
                        array(true, 'methodWithThisInDoubleQuotes'),
                     '$this in double quotes (complex)' =>
                        array(true, 'methodWithThisInDoubleQuotesWithBracketEscape'),
                     '$this in double quotes with function call (complex)' =>
                        array(true, 'methodWithThisInDoubleQuotesWithBracketEscapeAndFunctionCall'),
                     '$this in double quotes on multiple lines' =>
                        array(true, 'methodWithThisInDoubleQuotesBrokenOntoMultipleLines'),
                     '$this in double quotes on multiple lines(complex)' =>
                        array(true, 'methodWithThisInDoubleQuotesWithBracketEscapeBrokenOntoMultipleLines'),
                     '$this in double quotes with function call on multiple lines(complex)' =>
                        array(true, 'methodWithThisInDoubleQuotesWithBracketEscapeAndFunctionCallBrokenOntoMultipleLines'),
                     '$this commented out using // and #' =>
                        array(false, 'methodThatHasThisInSingleLineComments'),
                     '$this commented out using block comments' =>
                        array(false, 'methodThatHasThisInBlockComments'),
                     '$this commented out using NowBlock' =>
                        array(false, 'methodThatHasThisInNowDoc'),
                     '$this with escaped dollar sign in double quotes' =>
                        array(false, 'methodWithEscapedDollarSignThisInDoubleQuotes'),
                     '$this with escaped dollar sign in HereDoc' =>
                        array(false, 'methodWithEscapedDollarSignThisInHereDoc'),
                     'Variable name starting with $this' =>
                        array(false, 'methodWithVariableStartingWithThis'));
    }

    /**
     * Test function ClassMixer::hasInstanceContet correctly returns if a function is using instance context (the $this variable)
     *
     * @dataProvider  dataProviderClassMixerTestClassesThatHaveInstanceContext
     *
     * @param  bool   $has_instance_context          If the method does have instance context (expected return value of ClassMixer::hasInstanceContet)
     * @param  string $class_mixer_test_class_method Name of a method on ClassMixerTestClass to check hasInstanceContext
     */
    public function testHasInstanceContext(bool $has_instance_context, string $class_mixer_test_class_method)
    {
        $reflection_class = new ReflectionClass(ClassMixerTestClass::class);
        $reflection_method = $reflection_class->getMethod($class_mixer_test_class_method);
        $this->assertSame($has_instance_context, ClassMixer::hasInstanceContext($reflection_method));
    }
}
