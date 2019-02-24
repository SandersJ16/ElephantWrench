<?php

namespace ElephantWrench\Test;

use InvalidArgumentException;

use ElephantWrench\Test\Helpers\MixableTestClass;

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

    public function testAddingACombinatorForAFunctionNotOnBaseClassAndNotMixedInGetsCalledAndReturnsValue()
    {
        $expected_value = 'value';
        MixableTestClass::addCombinator('getValue', function(array $mixed_methods, array $args) use ($expected_value) {
            return $expected_value;
        });
        $mixable_class = new MixableTestClass();
        $this->assertEquals($expected_value, $mixable_class->getValue());
    }

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
                array(function(Taversable $a, Traversable $b) {}, true),
            'Combinator function with first parameter type hinted as array and second parameter type hinted as Traversable' =>
                array(function(array $a, Traversable $b) {}, true),
            'Combinator function with first parameter type hinted as Taversable and second parameter type hinted as array' =>
                array(function(Traversable $a, array $b) {}, true),
            'Combinator function with no arguments' =>
                array(function() {}, false),
            'Combinator function with 1 argument' =>
                array(function($a) {}, false),
            'Combinator function with more than 2 arguments, where all extra arguments do not have a default value' =>
                array(function($a, $b, $c, $d) {}, false),
            'Combinator function with more than 2 arguments, where some but not all extra arguments have a default value' =>
                array(function($a, $b, $c, $d = null) {}, false),
            );
    }

    /**
     * @dataProvider combinatorFunctionHeaderDataProvider
     *
     * @param  [type] $closure  [description]
     * @param  bool   $is_valid [description]
     * @return [type]           [description]
     */
    public function testAddingACombinatorWithoutProperFunctionParametersThrowExceptions($closure, bool $is_valid)
    {
        if (!$is_valid) {
            $this->expectException(InvalidArgumentException::class);
        }
        MixableTestClass::addCombinator('test', $closure);
    }
}

