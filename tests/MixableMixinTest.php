<?php

namespace ElephantWrench\Test;

use Error;

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
     * Test that a protected property added through a mixin can
     * NOT be accessed directly on an instance of the mixable class
     *
     * @expectedException \Error
     */
    public function testAccessingProtectedMixedPropertyDirectlyIsNotAllowed()
    {
        $this->expectException(Error::class);

        MixableTestClass::mixin(MixinClass::class);
        $mixable_class = new MixableTestClass();
        $mixable_class->protected_mixin_property;
    }

    /**
     * Test that a private property added through a mixin can
     * NOT be accessed directly on an instance of the mixable class
     *
     * @expectedException \Error
     */
    public function testAccessingPrivateMixedPropertyDirectlyIsNotAllowed()
    {
        $this->expectException(Error::class);

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
     * Test that a public method added through a mixed in class
     * can call a protected method added through that same class
     */
    public function testProtectedFunctionCanBeCalledFromInsideAnotherFunction()
    {
        MixableTestClass::mixin(MixinClass::class);
        $mixable_class = new MixableTestClass();

        $this->assertEquals('protected method', $mixable_class->publicMethodCallProtectedMethod());
    }

    /**
     * Test that a public method added through a mixed in class can call a private method added through that same class
     */
    public function testPrivateFunctionCanBeCalledFromInsideAnotherFunction()
    {
        MixableTestClass::mixin(MixinClass::class);
        $mixable_class = new MixableTestClass();

        $this->assertEquals('private method', $mixable_class->publicMethodCallPrivateMethod());
    }

    /**
     * Test that a protected property added through a mixed in class can be accessed through a function on that class
     */
    public function testProtectedPropertyCanBeAccessedFromInsideAClassFunction()
    {
        MixableTestClass::mixin(MixinClass::class);
        $mixable_class = new MixableTestClass();

        $this->assertEquals('protected mixin property', $mixable_class->publicMethodReturnProtectedProperty());
    }

    /**
     * Test that a private property added through a mixed in class can be accessed through a function on that class
     */
    public function testPrivatePropertyCanBeAccessedFromInsideAClassFunction()
    {
        MixableTestClass::mixin(MixinClass::class);
        $mixable_class = new MixableTestClass();

        $this->assertEquals('private mixin property', $mixable_class->publicMethodReturnPrivateProperty());
    }

    /**
     * Test that a protected property added through a mixed in class modified
     * on an instanticiated instance is modified
     */
    public function testModifiedProtectedPropertyReturnsModifiedValue()
    {
        MixableTestClass::mixin(MixinClass::class);
        $mixable_class = new MixableTestClass();

        $new_protected_property_value = 'new protected mixin property value';
        $mixable_class->publicMethodThatSetsProtectedProperty($new_protected_property_value);

        $this->assertEquals($new_protected_property_value, $mixable_class->publicMethodReturnProtectedProperty());
    }

    /**
     * Test that a private property added through a mixed in class modified
     * on an instanticiated instance is modified
     */
    public function testModifiedPrivatePropertyReturnsModifiedValue()
    {
        MixableTestClass::mixin(MixinClass::class);
        $mixable_class = new MixableTestClass();

        $new_private_property_value = 'new private mixin property value';
        $mixable_class->publicMethodThatSetsPrivateProperty($new_private_property_value);

        $this->assertEquals($new_private_property_value, $mixable_class->publicMethodReturnPrivateProperty());
    }

    /**
     * Test that a protected property added through a mixed in class modified
     * on an instanticiated instance remains protected
     *
     * @expectedException \Error
     */
    public function testModifiedProtectedPropertyRemainsProtected()
    {
        $this->expectException(Error::class);

        MixableTestClass::mixin(MixinClass::class);
        $mixable_class = new MixableTestClass();

        $mixable_class->publicMethodThatSetsProtectedProperty('new protected mixin property value');

        $mixable_class->protected_mixin_property;
    }

    /**
     * Test that a private property added through a mixed in class modified
     * on an instanticiated instance is modified and remains protected
     *
     * @expectedException \Error
     */
    public function testModifiedPrivatePropertyRemainsProtected()
    {
        $this->expectException(Error::class);

        MixableTestClass::mixin(MixinClass::class);
        $mixable_class = new MixableTestClass();

        $mixable_class->publicMethodThatSetsPrivateProperty('new private mixin property value');

        $mixable_class->private_mixin_property;
    }

    /**
     * Test that a protected property added through a mixed in class modified
     * on an instanticiated instance is modified only for the one instance of that class
     */
    public function testModifiedProtectedPropertyOnlyModifiedForSingleInstanceOfClass()
    {
        MixableTestClass::mixin(MixinClass::class);
        $mixable_class_1 = new MixableTestClass();
        $mixable_class_2 = new MixableTestClass();

        $mixable_class_1->publicMethodThatSetsProtectedProperty('new protected mixin property value');

        $this->assertNotEquals(
            $mixable_class_2->publicMethodReturnProtectedProperty(),
            $mixable_class_1->publicMethodReturnProtectedProperty()
        );
    }

    /**
     * Test that a private property added through a mixed in class modified
     * on an instanticiated instance is modified only for the one instance of that class
     */
    public function testModifiedPrivatePropertyOnlyModifiedForSingleInstanceOfClass()
    {
        MixableTestClass::mixin(MixinClass::class);
        $mixable_class_1 = new MixableTestClass();
        $mixable_class_2 = new MixableTestClass();

        $mixable_class_1->publicMethodThatSetsPrivateProperty('new private mixin property value');

        $this->assertNotEquals(
            $mixable_class_2->publicMethodReturnPrivateProperty(),
            $mixable_class_1->publicMethodReturnPrivateProperty()
        );
    }

    /**
     * Test that our __get function doesn't allow the returning of protected properties on the class
     *
     * @expectedException \Error
     */
    public function testGetFunctionDoesntAllowReturningOfNonMixedProtectedProperties()
    {
        $this->expectException(Error::class);

        $mixable_class = new MixableTestClass();

        $mixable_class->protected_property;
    }

    /**
     * Test that our __set function doesn't allow the setting of protected properties on the class
     *
     * @expectedException \Error
     */
    public function testSetFunctionDoesntAllowSettingOfNonMixedProtectedProperties()
    {
        $this->expectException(Error::class);

        $mixable_class = new MixableTestClass();

        $mixable_class->protected_property = 'test';
    }

    /**
     * Test that our __call function doesn't allow the calling of protected methods on the class
     *
     * @expectedException \Error
     */
    public function testCallFunctionDoesntAllowSettingOfNonMixedProtectedProperties()
    {
        $this->expectException(Error::class);

        $mixable_class = new MixableTestClass();

        $mixable_class->protectedNonMixedMethod();
    }
}
