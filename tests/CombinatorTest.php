<?php

namespace ElephantWrench\Test;

use Error;
use Traversable;
use ArrayObject;
use InvalidArgumentException;

use ElephantWrench\Test\Helpers\{MixableTestClass, MixableTestSubClass};

/**
 * This class tests the Mixable::Mix function
 */
class CombinatorTest extends ElephantWrenchBaseTestCase
{
    /**
     * Classes whose static variables should be reset between every test
     * (See `ElephantWrenchBaseTestCase::setUpBeforeClass()` and `ElephantWrenchBaseTestCase::setUp()`)
     *
     * @var array
     */
    protected static $reset_classes = array(MixableTestClass::class);

    /**
     * Test that after adding a combinator to a function that combinator is called from the mbase object
     */
    public function testAddingACombinatorForAFunctionNotOnBaseClassAndNotMixedInGetsCalledAndReturnsValue()
    {
        $expected_value = 'value';
        $function_name = 'getValue';
        MixableTestClass::mix($function_name, function () {
            return 'bad value';
        });
        MixableTestClass::addCombinator($function_name, function(array $mixed_methods, array $args) use ($expected_value) {
            return $expected_value;
        });
        $mixable_class = new MixableTestClass();
        $this->assertEquals($expected_value, $mixable_class->$function_name());
    }
    /**
     * Return test cases for testing combinator function signatures
     *
     * @return array An array of arrays where the first value of each internal array is a
     *               lambda function with no body and the second value is a boolean value
     *               for if the lambda function is a valid combinator function or not.
     */
    public function combinatorFunctionHeaderDataProvider() {
        return array(
            'Combinator function with no type hinting' =>
                array(function($a, $b) {}, true),
            'Combinator function with first parameter type hinted as an array' =>
                array(function(array $a, $b) {}, true),
            'Combinator function with second parameter type hinted as an array' =>
                array(function($a, array $b) {}, true),
            'Combinator function with first parameter type hinted as an array' =>
                array(function(Traversable $a, $b) {}, true),
            'Combinator function with second parameter type hinted as a Traversable' =>
                array(function($a, Traversable $b) {}, true),
            'Combinator function with both parameters type hinted as array' =>
                array(function(array $a, array $b) {}, true),
            'Combinator function with neither parameters type hinted as Traversable' =>
                array(function(Traversable $a, Traversable $b) {}, true),
            'Combinator function with first parameter type hinted as array and second parameter type hinted as Traversable' =>
                array(function(array $a, Traversable $b) {}, true),
            'Combinator function with first parameter type hinted as Taversable and second parameter type hinted as array' =>
                array(function(Traversable $a, array $b) {}, true),
            'Combinator function with first parameter type hinted as a class that implements Traversable' =>
                array(function(ArrayObject $a, $b) {}, false),
            'Combinator function with first parameter type hinted as something other than Traversable or array' =>
                array(function(string $a, $b) {}, false),
            'Combinator function with second parameter type hinted as something other than Traversable or array' =>
                array(function($a, int $b) {}, false),
            'Combinator function with first parameter type hinted as something other than Traversable or array and second parameter type hinted as Traversable' =>
                array(function(double $a, Traversable $b) {}, false),
            'Combinator function with second parameter type hinted as something other than Traversable or array and first parameter type hinted as array' =>
                array(function(array $a, bool $b) {}, false),
            'Combinator function with no arguments' =>
                array(function() {}, false),
            'Combinator function with 1 argument' =>
                array(function($a) {}, false),
            'Combinator function with more than 2 arguments, where all extra arguments do not have a default value' =>
                array(function($a, $b, $c, $d) {}, false),
            'Combinator function with more than 2 arguments, where some but not all extra arguments have a default value' =>
                array(function($a, $b, $c, $d = null) {}, false),
            'Combinator function with more than 2 arguments, where all extra arguments have a default value' =>
                array(function($a, $b, $c = null, $d = null) {}, true),
            'Combinator function with a specified return type' =>
                array(function($a, $b) : boolean {}, true)
            );
    }

    /**
     *  Testing for validity of combinators based on their function signatures
     *
     * @dataProvider combinatorFunctionHeaderDataProvider
     *
     * @param  callable $closure  Closure to add as a combinator function
     * @param  bool     $is_valid If this is a valid Combinator
     */
    public function testAddingACombinatorWithoutProperFunctionSignaturesThrowExceptions(callable $closure, bool $is_valid)
    {
        if (!$is_valid) {
            $this->expectException(InvalidArgumentException::class);
        }
        MixableTestClass::addCombinator('test', $closure);
    }

    /**
     * Test that a combinator has access to the correct number of mixed in functions
     */
    public function testCombinatorHasCorrectNumberOfMixedInFunctions()
    {
        $combinator_function_name = 'test';
        $combinator_counter = function($mixed_methods, $parameters) {
            return count($mixed_methods);
        };

        $mixable_test_class = new MixableTestClass();

        $mixable_test_class::addCombinator($combinator_function_name, $combinator_counter);
        $this->assertEquals(0, $mixable_test_class->$combinator_function_name());

        for ($i = 0; $i < 3; ++$i) {
            $mixable_test_class::mix($combinator_function_name, function() {});
            $this->assertEquals($i + 1, $mixable_test_class->$combinator_function_name());
        }
    }

    /**
     * Test that combinator has access to return values of mixed function when no parameters are passed to the mixed methods
     */
    public function testUsingReturnValuesOfMultipleMixedFunctionsInCombinator()
    {
        $combinator_function_name = 'number_of_heroes';
        $summation_combinator = function ($mixed_methods, $parameters) {
            $sum = 0;
            foreach ($mixed_methods as $mixed_method) {
                $sum += $mixed_method();
            }
            return $sum;
        };

        $mixable_test_class = new MixableTestClass();
        $mixable_test_class::addCombinator($combinator_function_name, $summation_combinator);

        $summation_values = array(4, 7, 9);
        foreach ($summation_values as $summation_value) {
            $mixable_test_class::mix($combinator_function_name, function() use($summation_value) {return $summation_value;});
        }

        $this->assertEquals(array_sum($summation_values), $mixable_test_class->$combinator_function_name());
    }

    /**
     * Test that all mixed methods are called by combinator and tht parameters are passed through to mixed methods properly
     */
    public function testUsingReturnValuesOfMultipleMixedFunctionsThatAcceptParametersInCombinator()
    {
        $combinator_function_name = 'power_of';
        $aggregate_combinator = function ($mixed_methods, $parameters) {
            $return_values = array();
            foreach ($mixed_methods as $mixed_method) {
                $return_values[] = $mixed_method(...$parameters);
            }
            return $return_values;
        };

        $mixable_test_class = new MixableTestClass();
        $mixable_test_class::addCombinator($combinator_function_name, $aggregate_combinator);

        for ($i = 1; $i <= 3; ++$i) {
            $mixable_test_class::mix($combinator_function_name, function($value) use($i) {return $value * $i;});
        }

        $function_inputs = array(5, 7, 11);
        foreach ($function_inputs as $function_input) {
            $this->assertEquals(array($function_input * 1, $function_input * 2, $function_input * 3),
                                $mixable_test_class->$combinator_function_name($function_input));
        }
    }

    /**
     * Test that mixed methods called by a combinator have access to the properties and functions of the class
     */
    public function testMixedMethodsCalledViaACombinatorHaveAccessToClassPropertiesAndMethods()
    {
        $combinator_function_name = 'get_property_or_call_function';
        $aggregate_combinator = function ($mixed_methods, $parameters) {
            $return_values = array();
            foreach ($mixed_methods as $mixed_method) {
                $return_values[] = $mixed_method(...$parameters);
            }
            return $return_values;
        };

        $mixable_test_class = new MixableTestClass();
        $mixable_test_class::addCombinator($combinator_function_name, $aggregate_combinator);
        $mixable_test_class::mix($combinator_function_name, function() {return $this->public_property;});
        $mixable_test_class::mix($combinator_function_name, function() {return self::$static_public_property;});
        $mixable_test_class::mix($combinator_function_name, function() {return $this->publicNonMixedMethod();});
        $mixable_test_class::mix($combinator_function_name, function() {return self::publicNonMixedStaticMethod();});
        $mixable_test_class::mix($combinator_function_name, function() {return $this->protected_property;});
        $mixable_test_class::mix($combinator_function_name, function() {return self::$static_protected_property;});
        $mixable_test_class::mix($combinator_function_name, function() {return $this->protectedNonMixedMethod();});
        $mixable_test_class::mix($combinator_function_name, function() {return self::protectedNonMixedStaticMethod();});
        $mixable_test_class::mix($combinator_function_name, function() {return $this->private_property;});
        $mixable_test_class::mix($combinator_function_name, function() {return self::$static_private_property;});
        $mixable_test_class::mix($combinator_function_name, function() {return $this->privateNonMixedMethod();});
        $mixable_test_class::mix($combinator_function_name, function() {return self::privateNonMixedStaticMethod();});

        $this->assertEquals(array($mixable_test_class->public_property,
                                  $mixable_test_class::$static_public_property,
                                  $mixable_test_class->publicNonMixedMethod(),
                                  $mixable_test_class::publicNonMixedStaticMethod(),
                                  $this->getNonPublicProperty($mixable_test_class, 'protected_property'),
                                  $this->getNonPublicProperty($mixable_test_class, 'static_protected_property'),
                                  $this->callNonPublicMethod($mixable_test_class, 'protectedNonMixedMethod'),
                                  $this->callNonPublicMethod($mixable_test_class, 'protectedNonMixedStaticMethod'),
                                  $this->getNonPublicProperty($mixable_test_class, 'private_property'),
                                  $this->getNonPublicProperty($mixable_test_class, 'static_private_property'),
                                  $this->callNonPublicMethod($mixable_test_class, 'privateNonMixedMethod'),
                                  $this->callNonPublicMethod($mixable_test_class, 'privateNonMixedStaticMethod')),
                            $mixable_test_class->$combinator_function_name());

    }

    /**
     * Data Provider that give functions that use private properties and call private methods of MixableTestClass
     */
    public function functionsUsingPrivatePropertiesAndMethodsDataProvider() {
        return array(
            'Function accessing a private property of parent class' =>
                array(function() {return $this->private_property;}),
            'Function accessing a private static property of parent class' =>
                array(function() {return self::$static_private_property;}),
            'Function calling a private method of parent class' =>
                array(function() {return $this->privateNonMixedMethod();}),
            'Function calling a private static method of parent class' =>
                array(function() {return self::privateNonMixedStaticMethod();}),
            );
    }

    /**
     * Test that mixed methods called from a combinator don't have access to properties or functions that are out of their context
     *
     * @dataProvider      functionsUsingPrivatePropertiesAndMethodsDataProvider
     *
     * @expectedException \Error
     */
    public function testMixedMethodsCalledViaACombinatorDoNotHaveAccessToPrivateClassPropertiesAndMethodsOfAParentClass($function_accessing_private_property_or_method) {

        $this->expectException(Error::class);

        $combinator_function_name = 'call_inaccesible_function';
        $call_all_combinator = function ($mixed_methods, $parameters) {
            foreach ($mixed_methods as $mixed_method) {
                $mixed_method(...$parameters);
            }
        };

        $mixable_test_class = new MixableTestSubClass();
        $mixable_test_class::addCombinator($combinator_function_name, $call_all_combinator);
        $mixable_test_class::mix($combinator_function_name, $function_accessing_private_property_or_method);

        $mixable_test_class->$combinator_function_name();
    }
}
