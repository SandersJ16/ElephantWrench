<?php

namespace ElephantWrench\Test;

use ElephantWrench\Test\Helpers\{MixableTestClass, MixableTestSubClass};

class TestMixable extends ElephantWrenchBaseTestCase
{
    protected static $reset_classes = array(MixableTestClass::class, MixableTestSubClass::class);

    public function testAddingAFunctionWithNoObjectOrClassContext()
    {
        $mixable_class = new MixableTestClass();
        $mixable_class->mix('add', function ($a, $b) {
            return $a + $b;
        });
        $this->assertEquals(5, $mixable_class->add(2, 3));
        $this->assertEquals(7, $mixable_class->add(6, 1));
    }

    public function testCallingAddedFunctionInMixableSubclass()
    {
        $mixable_class = new MixableTestClass();
        $mixable_subclass = new MixableTestSubClass();
        $mixable_class->mix('add', function ($a, $b) {
            return $a + $b;
        });
        $this->assertEquals(5, $mixable_subclass->add(2, 3));
        $this->assertEquals(7, $mixable_subclass->add(6, 1));
    }
}
