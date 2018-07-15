<?php

namespace ElephantWrench\Test;

use Error;
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

    /**
     * Test that a public property added through a mixin can be accessed directly on an instance of the mixable class
     */
    public function testAccessingPublicMixedProperty()
    {
        MixableTestClass::mixin(MixinClass::class);
        $mixin_class = new MixinClass();
        $mixable_class = new MixableTestClass();
        $this->assertEquals($mixin_class->public_mixin_property, $mixable_class->public_mixin_property);
    }

    /**
     * Test that a protected property added through a mixin can NOT be accessed directly on an instance of the mixable class
     *
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testAccessingProtectedMixedPropertyDirectlyIsNotAllowed()
    {
        $this->expectException(PHPUnit_Framework_Error_Notice::class);

        MixableTestClass::mixin(MixinClass::class);
        $mixable_class = new MixableTestClass();
        $mixable_class->protected_mixin_property;
    }

    /**
     * Test that a private property added through a mixin can NOT be accessed directly on an instance of the mixable class
     *
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testAccessingPrivateMixedPropertyDirectlyIsNotAllowed()
    {
        $this->expectException(PHPUnit_Framework_Error_Notice::class);

        MixableTestClass::mixin(MixinClass::class);
        $mixable_class = new MixableTestClass();
        $mixable_class->private_mixin_property;
    }

    /**
     * Test that we can set a public property added through a mixed in class
     */
    public function testSettingPublicPropertiesMixedIntoMixableClass()
    {
        MixableTestClass::mixin(MixinClass::class);
        $mixable_class = new MixableTestClass();
        $mixable_class->public_mixin_property = 'new value';
        $this->assertEquals('new value', $mixable_class->public_mixin_property);
    }

    /**
     * Test that modifying a public property added through a mixed in class on an instance
     * of that class doesn't modify the property on other instances of the class
     */
    public function testPublicPropertiesAreNotSharedAmongstMultipleInstancesOfAMixableClass()
    {
        MixableTestClass::mixin(MixinClass::class);
        $mixable_class_1 = new MixableTestClass();
        $mixable_class_2 = new MixableTestClass();
        $mixable_class_1->public_mixin_property = 'new value';
        $this->assertNotEquals($mixable_class_1->public_mixin_property, $mixable_class_2->public_mixin_property);
    }

    /**
     * Test calling a public method added through a mixed in class
     */
    public function testAccessingPublicMixedMethod()
    {
        MixableTestClass::mixin(MixinClass::class);
        $mixable_class = new MixableTestClass();
        $this->assertEquals('public method', $mixable_class->publicMethod());
    }

    /**
     * Test calling a protected method added through a mixed in class throws an error
     *
     * @expectedException \Error
     */
    public function testProtectedFunctionCantBeCalled()
    {
        $this->expectException(Error::class);

        MixableTestClass::mixin(MixinClass::class);
        $mixable_class = new MixableTestClass();

        $mixable_class->protectedMethod();
    }

    /**
     * Test calling a private method added through a mixed in class throws an error
     *
     * @expectedException \Error
     */
    public function testPrivateFunctionCantBeCalled()
    {
        $this->expectException(Error::class);

        MixableTestClass::mixin(MixinClass::class);
        $mixable_class = new MixableTestClass();

        $mixable_class->privateMethod();
    }

    /**
     * Test that a public method adde through a mixed in class can call a protected method added through that same class
     */
    public function testProtectedFunctionCanBeCalledFromInsideAnotherFunction()
    {
        MixableTestClass::mixin(MixinClass::class);
        $mixable_class = new MixableTestClass();

        $this->assertEquals('protected method', $mixable_class->publicMethodCallProtectedMethod());
    }

    /**
     * Test that a public method adde through a mixed in class can call a private method added through that same class
     */
    public function testPrivateFunctionCanBeCalledFromInsideAnotherFunction()
    {
        MixableTestClass::mixin(MixinClass::class);
        $mixable_class = new MixableTestClass();

        $this->assertEquals('private method', $mixable_class->publicMethodCallPrivateMethod());
    }
}
