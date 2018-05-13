<?php

namespace ElephantWrench\Test;

use ElephantWrench\Test\Helpers\{MixableTestClass, MixableTestSubClass};

class TestMixable extends ElephantWrenchBaseTestCase
{
    protected static $reset_classes = array(MixableTestClass::class, MixableTestSubClass::class);

    public function testAddingFunctionWithNoObjectOrClassContext()
    {
        $mixable_class = new MixableTestClass();
        $mixable_class::mix('add', function ($a, $b) {
            return $a + $b;
        });
        $this->assertEquals(5, $mixable_class->add(2, 3));
        $this->assertEquals(7, $mixable_class->add(6, 1));
    }

    public function testCallingAddedFunctionFromMixableSubclass()
    {
        $mixable_class = new MixableTestClass();
        $mixable_subclass = new MixableTestSubClass();
        $mixable_class::mix('multiply', function ($a, $b) {
            return $a * $b;
        });
        $this->assertEquals(60, $mixable_subclass->multiply(12, 5));
        $this->assertEquals(14, $mixable_subclass->multiply(7, 2));
    }

    public function testAddingFunctionThatUsesExistingMixableClassPublicProperty()
    {
        $mixable_class = new MixableTestClass();
        $mixable_class::mix('getPublicProperty', function() {
          return $this->public_property;
        });
        $this->assertEquals($mixable_class->public_property, $mixable_class->getPublicProperty());
    }
}
