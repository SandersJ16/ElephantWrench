<?php

namespace ElephantWrench\Test;

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
        MixableTestClass::addCombinator('getValue', function(array $mixed_methods, array $args) {
            return 'value';
        });
        $mixable_class = new MixableTestClass();
        $this->assertEquals('value', $mixable_class->getValue());
    }
}

