<?php

namespace ElephantWrench\Test;

use Error;
use ReflectionFunction;

use ElephantWrench\Core\Exception\ContextCallableException;
use ElephantWrench\Test\Helpers\{MixableTestClass, MixableTestSubClass};

/**
 * This class tests the Mixable::Mix function
 */
class MixableStaticMixTest extends ElephantWrenchBaseTestCase
{
    /**
     * Classes whose static variables should be reset between every test
     * (See `ElephantWrenchBaseTestCase::setUpBeforeClass()` and `ElephantWrenchBaseTestCase::setUp()`)
     *
     * @var array
     */
    protected static $reset_classes = array(MixableTestClass::class, MixableTestSubClass::class);

    public function testAddingStaticFunctionWithNoClassContext()
    {
        MixableTestClass::staticMix('add', function ($a, $b) {
            return $a + $b;
        });

        $mixable_class = new MixableTestClass();
        $this->assertEquals(5, $mixable_class::add(2, 3));
        $this->assertEquals(7, MixableTestClass::add(6, 1));
    }

    public function testCallingAddedStaticFunctionFromMixableSubclass()
    {
        MixableTestClass::staticMix('multiply', function ($a, $b) {
            return $a * $b;
        });

        $mixable_subclass = new MixableTestSubClass();
        $this->assertEquals(60, $mixable_subclass::multiply(12, 5));
        $this->assertEquals(14, MixableTestSubClass::multiply(7, 2));
    }

    public function testAddingNonStaticFunctionStaticallyStaticFunction()
    {
        $this->expectException(ContextCallableException::class);

        MixableTestClass::staticMix('getInstanceProperty', function () {
            return $this->public_property;
        });
    }

    /**
     * Test that an added static function can use a public static property
     * defined on the Mixable using self.
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassStaticPublicProperty()
    {
        MixableTestClass::staticMix('getStaticPublicProperty', function () {
            return self::$static_public_property;
        });
        $this->assertEquals(MixableTestClass::$static_public_property, MixableTestClass::getStaticPublicProperty());
    }

    /**
     * Test that an added static function can use a protected static property
     * defined on the Mixable using self.
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassStaticProtectedProperty()
    {
        $mixable_class = new MixableTestClass();
        $mixable_class::staticMix('getStaticProtectedProperty', function () {
            return self::$static_protected_property;
        });
        $this->assertEquals($this->getNonPublicProperty($mixable_class, 'static_protected_property'), MixableTestClass::getStaticProtectedProperty());
    }

    /**
     * Test that an added static function can use a private static property
     * defined on the Mixable using self.
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassStaticPrivateProperty()
    {
        $mixable_class = new MixableTestClass();
        $mixable_class::staticMix('getStaticPrivateProperty', function () {
            return self::$static_private_property;
        });
        $this->assertEquals($this->getNonPublicProperty($mixable_class, 'static_private_property'), MixableTestClass::getStaticPrivateProperty());
    }

    /**
     * Test that an added static function can use a public static property
     * defined on the Mixable using static from a child class.
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassStaticPublicPropertyCallableFromMixableSubclassWhenUsingStaticToAccessProperty()
    {
        MixableTestClass::staticMix('getStaticPublicProperty', function () {
            return static::$static_public_property;
        });
        $this->assertEquals(MixableTestSubClass::$static_public_property, MixableTestSubClass::getStaticPublicProperty());
    }

    /**
     * Test that an added static function can use a protected static property
     * defined on the Mixable using static from a child class.
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassStaticProtectedPropertyCallableFromMixableSubclassWhenUsingStaticToAccessProperty()
    {
        $mixable_subclass = new MixableTestSubClass();
        MixableTestClass::staticMix('getStaticProtectedProperty', function () {
            return static::$static_protected_property;
        });
        $this->assertEquals($this->getNonPublicProperty($mixable_subclass, 'static_protected_property'), MixableTestSubClass::getStaticProtectedProperty());
    }

    /**
     * Test that an added static function can NOT use a private static property
     * defined on the Mixable using static from a child class.
     *
     * @expectedException Error
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassStaticPrivatePropertyCallableFromMixableSubclassWhenUsingStaticToAccessProperty()
    {
        $this->expectException(Error::class);

        MixableTestClass::staticMix('getStaticPrivateProperty', function () {
            return static::$static_private_property;
        });

        //Should throw an error for cannot access property `static_private_property`
        //since getStaticPrivateProperty was added to `MixableTestSubClass` which can not access
        //the private properties of its parent `MixableTestClass`
        MixableTestSubClass::getStaticPrivateProperty();
    }

    /**
     * Test that an added static function using a public static property
     * defined on the Mixable called using `self` is callable from a
     * subclass of the mixable and uses the parent's property not the
     * subclass's property
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassStaticPublicPropertyCallableFromMixableSubclassWhenUsingSelfToAccessProperty()
    {
        MixableTestClass::staticMix('getStaticPublicProperty', function () {
            return self::$static_public_property;
        });
        $this->assertEquals(MixableTestClass::$static_public_property, MixableTestSubClass::getStaticPublicProperty());
    }

    /**
     * Test that an added static function using a protected static property
     * defined on the Mixable called using `self` is callable from a
     * subclass of the mixable and uses the parent's property not the
     * subclass's property
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassStaticProtectedPropertyCallableFromMixableSubclassWhenUsingSelfToAccessProperty()
    {
        $mixable_class = new MixableTestClass();
        MixableTestClass::staticMix('getStaticProtectedProperty', function () {
            return self::$static_protected_property;
        });
        $this->assertEquals($this->getNonPublicProperty($mixable_class, 'static_protected_property'), MixableTestSubClass::getStaticProtectedProperty());
    }

    /**
     * Test that an added static function using a private static property
     * defined on the Mixable called using `self` is callable from a
     * subclass of the mixable and uses the parent's property not the
     * subclass's property
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassStaticPrivatePropertyCallableFromMixableSubclassWhenUsingSelfToAccessProperty()
    {
        $mixable_class = new MixableTestClass();
        MixableTestClass::staticMix('getStaticPrivateProperty', function () {
            return self::$static_private_property;
        });
        $this->assertEquals($this->getNonPublicProperty($mixable_class, 'static_private_property'), MixableTestSubClass::getStaticPrivateProperty());
    }

    /**
     * Test that an added static function using a public static property
     * defined on the Mixable and overriden on a Subclass of the Mixable
     * called using `self` is callable when added to the subclass of the mixable
     * and uses the subclass's property not the parent's
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassStaticPublicPropertyToMixableSubclassThatOverridesParentsProperty()
    {
        MixableTestSubClass::staticMix('getStaticPublicProperty', function () {
            return self::$static_public_property;
        });
        $this->assertEquals(MixableTestSubClass::$static_public_property, MixableTestSubClass::getStaticPublicProperty());
    }

    /**
     * Test that an added static function using a protected static property
     * defined on the Mixable and overriden on a Subclass of the Mixable
     * called using `self` is callable when added to the subclass of the mixable
     * and uses the subclass's property not the parent's
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassStaticProtectedPropertyToMixableSubclassThatOverridesParentsProperty()
    {
        $mixable_subclass = new MixableTestSubClass();
        MixableTestSubClass::staticMix('getStaticProtectedProperty', function () {
            return self::$static_protected_property;
        });
        $this->assertEquals($this->getNonPublicProperty($mixable_subclass, 'static_protected_property'), MixableTestSubClass::getStaticProtectedProperty());
    }

    /**
     * Test that an added static function using a private static property
     * defined on the Mixable and called using `self` is not accesible
     * when added to the subclass of the mixable
     *
     * @expectedException Error
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassStaticPrivatePropertyToMixableSubclassThatOverridesParentsProperty()
    {
        $this->expectException(Error::class);

        $mixable_subclass = new MixableTestSubClass();
        MixableTestSubClass::staticMix('getStaticPrivateProperty', function () {
            return self::$static_private_property;
        });

        MixableTestSubClass::getStaticPrivateProperty();
    }

    /**
     * Test that an added static function can use a public static method
     * defined on the Mixable using `self`.
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassPublicMethodWithSelf()
    {
        MixableTestClass::staticMix('callStaticPublicMethod', function () {
            return self::publicNonMixedStaticMethod();
        });
        $this->assertEquals(MixableTestClass::publicNonMixedStaticMethod(), MixableTestClass::callStaticPublicMethod());
    }

    /**
     * Test that an added static function can use a protected static method
     * defined on the Mixable using `self`.
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassProtectedMethodWithSelf()
    {
        $mixable_class = new MixableTestClass();
        $mixable_class::staticMix('callStaticProtectedMethod', function () {
            return self::protectedNonMixedStaticMethod();
        });
        $this->assertEquals($this->callNonPublicMethod($mixable_class, 'protectedNonMixedStaticMethod'), MixableTestClass::callStaticProtectedMethod());
    }

    /**
     * Test that an added static function can use a private static method
     * defined on the Mixable using `self`.
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassPrivateMethodWithSelf()
    {
        $mixable_class = new MixableTestClass();
        $mixable_class::staticMix('callStaticPrivateMethod', function () {
            return self::privateNonMixedStaticMethod();
        });
        $this->assertEquals($this->callNonPublicMethod($mixable_class, 'privateNonMixedStaticMethod'), MixableTestClass::callStaticPrivateMethod());
    }

    /**
     * Test that an added static function can use a public static method
     * defined on the Mixable using `self` from a subclass of the mixable and returns
     * the value of the method in the mixable and not the subclass of the mixable.
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassPublicMethodFromSubclassWithSelf()
    {
        MixableTestClass::staticMix('callStaticPublicMethod', function () {
            return self::publicNonMixedStaticMethod();
        });
        $this->assertEquals(MixableTestClass::publicNonMixedStaticMethod(), MixableTestSubClass::callStaticPublicMethod());
    }

    /**
     * Test that an added static function can use a protected static method
     * defined on the Mixable using `self` from a subclass of the mixable and returns
     * the value of the method in the mixable and not the subclass of the mixable.
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassProtectedMethodFromSubclassWithSelf()
    {
        $mixable_class = new MixableTestClass();
        $mixable_class::staticMix('callStaticProtectedMethod', function () {
            return self::protectedNonMixedStaticMethod();
        });
        $this->assertEquals($this->callNonPublicMethod($mixable_class, 'protectedNonMixedStaticMethod'), MixableTestSubClass::callStaticProtectedMethod());
    }

    /**
     * Test that an added static function can use a private static method
     * defined on the Mixable using `self` from a subclass of the mixable.
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassPrivateMethodFromSubclassWithSelf()
    {
        $mixable_class = new MixableTestClass();
        $mixable_subclass = new MixableTestSubClass();
        $mixable_class::staticMix('callPrivateStaticMethod', function () {
            return self::privateNonMixedStaticMethod();
        });

        //Shouldn't thow an exception since the function was registered to a class that has access to this private method
        $this->assertEquals($this->callNonPublicMethod($mixable_class, 'privateNonMixedStaticMethod'), MixableTestSubClass::callPrivateStaticMethod());
    }

    /**
     * Test that a static function added to the subclass of a Mixable can
     * NOT use a private static method defined on the Mixable with `self`
     *
     * @expectedException Error
     */
    public function testAddingFunctionThatUsesParentExistingMixableClassPrivateMethodFromSubclassWithSelf()
    {
        $this->expectException(Error::class);

        MixableTestSubClass::staticMix('callPrivateStaticMethod', function () {
            return self::privateNonMixedStaticMethod();
        });

        //Should throw an exception for undefined method `privateNonMixedStaticMethod`
        //since callPrivateStaticMethod was added to `MixableTestSubClass` which can not access
        //the private methods of its parent `MixableTestClass`
        MixableTestSubClass::callPrivateStaticMethod();
    }

    /**
     * Test that an added static function can use a public static method
     * defined on the Mixable using `static`.
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassPublicMethodWithStatic()
    {
        MixableTestClass::staticMix('callStaticPublicMethod', function () {
            return static::publicNonMixedStaticMethod();
        });
        $this->assertEquals(MixableTestClass::publicNonMixedStaticMethod(), MixableTestClass::callStaticPublicMethod());
    }

    /**
     * Test that an added static function can use a protected static method
     * defined on the Mixable using `static`.
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassProtectedMethodWithStatic()
    {
        $mixable_class = new MixableTestClass();
        $mixable_class::staticMix('callStaticProtectedMethod', function () {
            return static::protectedNonMixedStaticMethod();
        });
        $this->assertEquals($this->callNonPublicMethod($mixable_class, 'protectedNonMixedStaticMethod'), MixableTestClass::callStaticProtectedMethod());
    }

    /**
     * Test that an added static function can use a private static method
     * defined on the Mixable using `static`.
     *
     * Not anticipated behaviour (was expecting an error) but this works in regular PHP; try:
     * ```
     * class C {private static function secret() {return 'secret';} public static function test() { return static::secret();}}
     * print C::test(); //Output: "secret"
     * ```
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassPrivateMethodWithStatic()
    {
        $mixable_class = new MixableTestClass();
        $mixable_class::staticMix('callStaticPrivateMethod', function () {
            return static::privateNonMixedStaticMethod();
        });

        MixableTestClass::callStaticPrivateMethod();
    }

    /**
     * Test that an added static function can use a public static method
     * defined on the Mixable using `static` from a subclass of the mixable and returns
     * the value of the method in the subclass of the mixable and not the mixable.
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassPublicMethodFromSubclassWithStatic()
    {
        MixableTestClass::staticMix('callStaticPublicMethod', function () {
            return static::publicNonMixedStaticMethod();
        });
        $this->assertEquals(MixableTestSubClass::publicNonMixedStaticMethod(), MixableTestSubClass::callStaticPublicMethod());
    }

    /**
     * Test that an added static function can use a protected static method
     * defined on the Mixable using `static` from a subclass of the mixable and returns
     * the value of the method in the subclass of the mixable and not the mixable.
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassProtectedMethodFromSubclassWithStatic()
    {
        $mixable_subclass = new MixableTestSubClass();
        MixableTestClass::staticMix('callStaticProtectedMethod', function () {
            return static::protectedNonMixedStaticMethod();
        });
        $this->assertEquals($this->callNonPublicMethod($mixable_subclass, 'protectedNonMixedStaticMethod'), MixableTestSubClass::callStaticProtectedMethod());
    }

    /**
     * Test that an added static function can use a private static method
     * defined on the Mixable using `static` from a subclass of the mixable.
     *
     * Not anticipated behaviour (was expecting an error) but this works in regular PHP; try:
     * ```
     * class C {private static function secret() {return 'secret';} public static function test() { return static::secret();}}
     * class D extends C {}
     * print D::test(); //Output: "secret"
     * ```
     */
    public function testAddingStaticFunctionThatUsesExistingMixableClassPrivateMethodFromSubclassWithStatic()
    {
        //$this->expectException(Error::class);
        $mixable_subclass = new MixableTestSubClass();
        MixableTestClass::staticMix('callPrivateStaticMethod', function () {
            return static::privateNonMixedStaticMethod();
        });

        $this->assertEquals($this->callNonPublicMethod($mixable_subclass, 'privateNonMixedStaticMethod'), MixableTestSubClass::callPrivateStaticMethod());
    }

    /**
     * Test that a static function added to the subclass of a Mixable can
     * NOT use a private static method defined on the Mixable with `static`
     *
     * @expectedException Error
     */
    public function testAddingFunctionThatUsesParentExistingMixableClassPrivateMethodFromSubclassWithStatic()
    {
        $this->expectException(Error::class);

        MixableTestSubClass::staticMix('callPrivateStaticMethod', function () {
            return static::privateNonMixedStaticMethod();
        });

        //Should throw an exception for undefined method `privateNonMixedStaticMethod`
        //since callPrivateStaticMethod was added to `MixableTestSubClass` which can not access
        //the private methods of its parent `MixableTestClass`
        MixableTestSubClass::callPrivateStaticMethod();
    }
}
