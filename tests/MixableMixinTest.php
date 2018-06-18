<?php

namespace ElephantWrench\Test;

use PHPUnit_Framework_Error_Notice;

use ElephantWrench\Test\Helpers\{MixableTestClass, MixableTestSubClass, MixinClass};

/**
 * This class tests the Mixable::Mixin function
 */
class MixableMixinTest extends ElephantWrenchBaseTestCase
{
    /**
     * Classes whose static variables should be reset between every test
     * (See `ElephantWrenchBaseTestCase::setUpBeforeClass()` and `ElephantWrenchBaseTestCase::setUp()`)
     *
     * @var array
     */
    protected static $reset_classes = array(MixableTestClass::class, MixableTestSubClass::class);

    public function testAccessingPublicMixedProperty()
    {
        MixableTestClass::mixin(MixinClass::class);
        $mixin_class = new MixinClass();
        $mixable_class = new MixableTestClass();
        $this->assertEquals($mixin_class->public_mixin_property, $mixable_class->public_mixin_property);
    }

    public function testAccessingProtectedMixedPropertyDirectlyIsNotAllowed()
    {
        $this->expectException(PHPUnit_Framework_Error_Notice::class);

        MixableTestClass::mixin(MixinClass::class);
        $mixable_class = new MixableTestClass();
        $mixable_class->protected_mixin_property;
    }

    public function testAccessingPrivtedMixedPropertyDirectlyIsNotAllowed()
    {
        $this->expectException(PHPUnit_Framework_Error_Notice::class);

        MixableTestClass::mixin(MixinClass::class);
        $mixable_class = new MixableTestClass();
        $mixable_class->private_mixin_property;
    }
}
