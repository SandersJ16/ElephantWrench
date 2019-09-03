<?php

namespace ElephantWrench\Test;

use Error;

use ElephantWrench\Test\Helpers\{MixableTestClass, MixableTestSubClass};

/**
 * This class tests the Mixable::Mix function
 */
class MixableMixTest extends ElephantWrenchBaseTestCase
{
    /**
     * Classes whose static variables should be reset between every test
     * (See `ElephantWrenchBaseTestCase::setUpBeforeClass()` and `ElephantWrenchBaseTestCase::setUp()`)
     *
     * @var array
     */
    protected static $reset_classes = array(MixableTestClass::class, MixableTestSubClass::class);

    /**
     * Test if after adding a basic function to a Mixable class
     * the function is callable on an instance of that class
     */
    public function testAddingFunctionWithNoObjectOrClassContext()
    {
        $mixable_class = new MixableTestClass();
        $mixable_class::mix('add', function ($a, $b) {
            return $a + $b;
        });
        $this->assertEquals(5, $mixable_class->add(2, 3));
        $this->assertEquals(7, $mixable_class->add(6, 1));
    }

    /**
     * Test if after adding a basic function to a Mixable class
     * the function is callable from a child of that class
     */
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

    /**
     * Test that an added function can use a public non-static property
     * defined on the Mixable using `$this`.
     */
    public function testAddingFunctionThatUsesExistingMixableClassPublicProperty()
    {
        $mixable_class = new MixableTestClass();
        $mixable_class::mix('getPublicProperty', function () {
            return $this->public_property;
        });
        $this->assertEquals($mixable_class->public_property, $mixable_class->getPublicProperty());
    }

    /**
     * Test that an added function can use a protected non-static property
     * defined on the Mixable using `$this`.
     */
    public function testAddingFunctionThatUsesExistingMixableClassProtectedProperty()
    {
        $mixable_class = new MixableTestClass();
        $mixable_class::mix('getProtectedProperty', function () {
            return $this->protected_property;
        });
        $this->assertEquals($this->getNonPublicProperty($mixable_class, 'protected_property'), $mixable_class->getProtectedProperty());
    }

    /**
     * Test that an added function can use a private non-static property
     * defined on the Mixable using `$this`.
     */
    public function testAddingFunctionThatUsesExistingMixableClassPrivateProperty()
    {
        $mixable_class = new MixableTestClass();
        $mixable_class::mix('getPrivateProperty', function () {
            return $this->private_property;
        });
        $this->assertEquals($this->getNonPublicProperty($mixable_class, 'private_property'), $mixable_class->getPrivateProperty());
    }

    /**
     * Test that an added function can use a public non-static property
     * defined on the Mixable using `$this` from a subclass of the mixable.
     */
    public function testAddingFunctionThatUsesExistingMixableClassPublicPropertyFromSubclass()
    {
        $mixable_class = new MixableTestClass();
        $mixable_subclass = new MixableTestSubClass();
        $mixable_class::mix('getPublicProperty', function () {
            return $this->public_property;
        });
        $this->assertEquals($mixable_subclass->public_property, $mixable_subclass->getPublicProperty());
    }

    /**
     * Test that an added function can use a protected non-static property
     * defined on the Mixable using `$this` from a subclass of the mixable.
     */
    public function testAddingFunctionThatUsesExistingMixableClassProtectedPropertyFromSubclass()
    {
        $mixable_class = new MixableTestClass();
        $mixable_subclass = new MixableTestSubClass();
        $mixable_class::mix('getProtectedProperty', function () {
            return $this->protected_property;
        });
        $this->assertEquals($this->getNonPublicProperty($mixable_subclass, 'protected_property'), $mixable_subclass->getProtectedProperty());
    }

    /**
     * Test that an added function can use a private non-static property
     * defined on the Mixable using `$this` from a subclass of the mixable.
     */
    public function testAddingFunctionThatUsesExistingMixableClassPrivatePropertyFromSubclass()
    {
        $mixable_class = new MixableTestClass();
        $mixable_subclass = new MixableTestSubClass();
        $mixable_class::mix('getPrivateProperty', function () {
            return $this->private_property;
        });

        //Shouldn't thow an exception since the function was registered to a class that has access to this private property
        $this->assertEquals($this->getNonPublicProperty($mixable_class, 'private_property'), $mixable_subclass->getPrivateProperty());
    }

    /**
     * Test that a function added to the subclass of a Mixable can
     * NOT use a private non-static property defined on the Mixable
     *
     * @expectedException \Error
     */
    public function testAddingFunctionThatUsesParentExistingMixableClassPrivatePropertyFromSubclass()
    {
        $this->expectException(Error::class);

        $mixable_subclass = new MixableTestSubClass();
        $mixable_subclass::mix('getPrivateProperty', function () {
            return $this->private_property;
        });

        //Should throw an exception for undefined property `private_property`
        //since getPrivateProperty was added to `MixableTestSubClass` which can not access
        //the private properties of its parent `MixableTestClass`
        $mixable_subclass->getPrivateProperty();
    }

    /**
     * Test that an added function can use a public static property
     * defined on the Mixable using self.
     */
    public function testAddingFunctionThatUsesExistingMixableClassStaticPublicProperty()
    {
        $mixable_class = new MixableTestClass();
        $mixable_class::mix('getStaticPublicProperty', function () {
            return self::$static_public_property;
        });
        $this->assertEquals($mixable_class::$static_public_property, $mixable_class->getStaticPublicProperty());
    }

    /**
     * Test that an added function can use a protected static property
     * defined on the Mixable using self.
     */
    public function testAddingFunctionThatUsesExistingMixableClassStaticProtectedProperty()
    {
        $mixable_class = new MixableTestClass();
        $mixable_class::mix('getStaticProtectedProperty', function () {
            return self::$static_protected_property;
        });
        $this->assertEquals($this->getNonPublicProperty($mixable_class, 'static_protected_property'), $mixable_class->getStaticProtectedProperty());
    }

    /**
     * Test that an added function can use a private static property
     * defined on the Mixable using self.
     */
    public function testAddingFunctionThatUsesExistingMixableClassStaticPrivateProperty()
    {
        $mixable_class = new MixableTestClass();
        $mixable_class::mix('getStaticPrivateProperty', function () {
            return self::$static_private_property;
        });
        $this->assertEquals($this->getNonPublicProperty($mixable_class, 'static_private_property'), $mixable_class->getStaticPrivateProperty());
    }

    /**
     * Test that an added function can use a public static property
     * defined on the Mixable using static from a child class.
     */
    public function testAddingFunctionThatUsesExistingMixableClassStaticPublicPropertyCallableFromMixableSubclassWhenUsingStaticToAccessProperty()
    {
        $mixable_subclass = new MixableTestSubClass();
        MixableTestClass::mix('getStaticPublicProperty', function () {
            return static::$static_public_property;
        });
        $this->assertEquals($mixable_subclass::$static_public_property, $mixable_subclass->getStaticPublicProperty());
    }

    /**
     * Test that an added function can use a protected static property
     * defined on the Mixable using static from a child class.
     */
    public function testAddingFunctionThatUsesExistingMixableClassStaticProtectedPropertyCallableFromMixableSubclassWhenUsingStaticToAccessProperty()
    {
        $mixable_subclass = new MixableTestSubClass();
        MixableTestClass::mix('getStaticProtectedProperty', function () {
            return static::$static_protected_property;
        });
        $this->assertEquals($this->getNonPublicProperty($mixable_subclass, 'static_protected_property'), $mixable_subclass->getStaticProtectedProperty());
    }

    /**
     * Test that an added function can NOT use a private static property
     * defined on the Mixable using static from a child class.
     *
     * @expectedException \Error
     */
    public function testAddingFunctionThatUsesExistingMixableClassStaticPrivatePropertyCallableFromMixableSubclassWhenUsingStaticToAccessProperty()
    {
        $this->expectException(Error::class);

        $mixable_subclass = new MixableTestSubClass();
        MixableTestClass::mix('getStaticPrivateProperty', function () {
            return static::$static_private_property;
        });

        //Should throw an error for cannot access property `static_private_property`
        //since getStaticPrivateProperty was added to `MixableTestSubClass` which can not access
        //the private properties of its parent `MixableTestClass`
        $mixable_subclass->getStaticPrivateProperty();
    }

    /**
     * Test that an added function using a public static property
     * defined on the Mixable called using `self` is callable from a
     * subclass of the mixable and uses the parent's property not the
     * subclass's property
     */
    public function testAddingFunctionThatUsesExistingMixableClassStaticPublicPropertyCallableFromMixableSubclassWhenUsingSelfToAccessProperty()
    {
        $mixable_subclass = new MixableTestSubClass();
        MixableTestClass::mix('getStaticPublicProperty', function () {
            return self::$static_public_property;
        });
        $this->assertEquals(MixableTestClass::$static_public_property, $mixable_subclass->getStaticPublicProperty());
    }

    /**
     * Test that an added function using a protected static property
     * defined on the Mixable called using `self` is callable from a
     * subclass of the mixable and uses the parent's property not the
     * subclass's property
     */
    public function testAddingFunctionThatUsesExistingMixableClassStaticProtectedPropertyCallableFromMixableSubclassWhenUsingSelfToAccessProperty()
    {
        $mixable_class = new MixableTestClass();
        $mixable_subclass = new MixableTestSubClass();
        MixableTestClass::mix('getStaticProtectedProperty', function () {
            return self::$static_protected_property;
        });
        $this->assertEquals($this->getNonPublicProperty($mixable_class, 'static_protected_property'), $mixable_subclass->getStaticProtectedProperty());
    }

    /**
     * Test that an added function using a private static property
     * defined on the Mixable called using `self` is callable from a
     * subclass of the mixable and uses the parent's property not the
     * subclass's property
     */
    public function testAddingFunctionThatUsesExistingMixableClassStaticPrivatePropertyCallableFromMixableSubclassWhenUsingSelfToAccessProperty()
    {
        $mixable_class = new MixableTestClass();
        $mixable_subclass = new MixableTestSubClass();
        MixableTestClass::mix('getStaticPrivateProperty', function () {
            return self::$static_private_property;
        });
        $this->assertEquals($this->getNonPublicProperty($mixable_class, 'static_private_property'), $mixable_subclass->getStaticPrivateProperty());
    }

    /**
     * Test that an added function using a public static property
     * defined on the Mixable and overriden on a Subclass of the Mixable
     * called using `self` is callable when added to the subclass of the mixable
     * and uses the subclass's property not the parent's
     */
    public function testAddingFunctionThatUsesExistingMixableClassStaticPublicPropertyToMixableSubclassThatOverridesParentsProperty()
    {
        $mixable_subclass = new MixableTestSubClass();
        MixableTestSubClass::mix('getStaticPublicProperty', function () {
            return self::$static_public_property;
        });
        $this->assertEquals(MixableTestSubClass::$static_public_property, $mixable_subclass->getStaticPublicProperty());
    }

    /**
     * Test that an added function using a protected static property
     * defined on the Mixable and overriden on a Subclass of the Mixable
     * called using `self` is callable when added to the subclass of the mixable
     * and uses the subclass's property not the parent's
     */
    public function testAddingFunctionThatUsesExistingMixableClassStaticProtectedPropertyToMixableSubclassThatOverridesParentsProperty()
    {
        $mixable_subclass = new MixableTestSubClass();
        MixableTestSubClass::mix('getStaticProtectedProperty', function () {
            return self::$static_protected_property;
        });
        $this->assertEquals($this->getNonPublicProperty($mixable_subclass, 'static_protected_property'), $mixable_subclass->getStaticProtectedProperty());
    }

    /**
     * Test that an added function using a private static property
     * defined on the Mixable and called using `self` is not accesible
     * when added to the subclass of the mixable
     *
     * @expectedException \Error
     */
    public function testAddingFunctionThatUsesExistingMixableClassStaticPrivatePropertyToMixableSubclassThatOverridesParentsProperty()
    {
        $this->expectException(Error::class);

        $mixable_subclass = new MixableTestSubClass();
        MixableTestSubClass::mix('getStaticPrivateProperty', function () {
            return self::$static_private_property;
        });
        $mixable_subclass->getStaticPrivateProperty();
    }

    /**
     * Test that an added function can use a public non-static method
     * defined on the Mixable using `$this`.
     */
    public function testAddingFunctionThatUsesExistingMixableClassPublicMethod()
    {
        $mixable_class = new MixableTestClass();
        $mixable_class::mix('callPublicMethod', function () {
            return $this->publicNonMixedMethod();
        });
        $this->assertEquals($mixable_class->publicNonMixedMethod(), $mixable_class->callPublicMethod());
    }

    /**
     * Test that an added function can use a protected non-static method
     * defined on the Mixable using `$this`.
     */
    public function testAddingFunctionThatUsesExistingMixableClassProtectedMethod()
    {
        $mixable_class = new MixableTestClass();
        $mixable_class::mix('callProtectedMethod', function () {
            return $this->protectedNonMixedMethod();
        });
        $this->assertEquals($this->callNonPublicMethod($mixable_class, 'protectedNonMixedMethod'), $mixable_class->callProtectedMethod());
    }

    /**
     * Test that an added function can use a private non-static method
     * defined on the Mixable using `$this`.
     */
    public function testAddingFunctionThatUsesExistingMixableClassPrivateMethod()
    {
        $mixable_class = new MixableTestClass();
        $mixable_class::mix('callPrivateMethod', function () {
            return $this->privateNonMixedMethod();
        });
        $this->assertEquals($this->callNonPublicMethod($mixable_class, 'privateNonMixedMethod'), $mixable_class->callPrivateMethod());
    }

    /**
     * Test that an added function can use a public non-static method
     * defined on the Mixable using `$this` from a subclass of the mixable.
     */
    public function testAddingFunctionThatUsesExistingMixableClassPublicMethodFromSubclass()
    {
        $mixable_class = new MixableTestClass();
        $mixable_subclass = new MixableTestSubClass();
        $mixable_class::mix('callPublicMethod', function () {
            return $this->publicNonMixedMethod();
        });
        $this->assertEquals($mixable_subclass->publicNonMixedMethod(), $mixable_subclass->callPublicMethod());
    }

    /**
     * Test that an added function can use a protected non-static method
     * defined on the Mixable using `$this` from a subclass of the mixable.
     */
    public function testAddingFunctionThatUsesExistingMixableClassProtectedMethodFromSubclass()
    {
        $mixable_class = new MixableTestClass();
        $mixable_subclass = new MixableTestSubClass();
        $mixable_class::mix('callProtectedMethod', function () {
            return $this->protectedNonMixedMethod();
        });
        $this->assertEquals($this->callNonPublicMethod($mixable_subclass, 'protectedNonMixedMethod'), $mixable_subclass->callProtectedMethod());
    }

    /**
     * Test that an added function can use a private non-static method
     * defined on the Mixable using `$this` from a subclass of the mixable.
     */
    public function testAddingFunctionThatUsesExistingMixableClassPrivateMethodFromSubclass()
    {
        $mixable_class = new MixableTestClass();
        $mixable_subclass = new MixableTestSubClass();
        $mixable_class::mix('callPrivateMethod', function () {
            return $this->privateNonMixedMethod();
        });

        //Shouldn't thow an exception since the function was registered to a class that has access to this private method
        $this->assertEquals($this->callNonPublicMethod($mixable_class, 'privateNonMixedMethod'), $mixable_subclass->callPrivateMethod());
    }

    /**
     * Test that a function added to the subclass of a Mixable can
     * NOT use a private non-static method defined on the Mixable
     *
     * @expectedException \Error
     */
    public function testAddingFunctionThatUsesParentExistingMixableClassPrivateMethodFromSubclass()
    {
        $this->expectException(Error::class);

        $mixable_subclass = new MixableTestSubClass();
        $mixable_subclass::mix('callPrivateMethod', function () {
            return $this->privateNonMixedMethod();
        });

        //Should throw an exception for undefined method `privateNonMixedMethod`
        //since callPrivateMethod was added to `MixableTestSubClass` which can not access
        //the private methods of its parent `MixableTestClass`
        $mixable_subclass->callPrivateMethod();
    }

    /**
     * Test that when multiple functions are mixed into a class with the same name,
     * the last function mixed in is the one that is used when called on the mixable class
     */
    public function testAddingMultipleFunctionsWithSameNameThatLastAddedFunctionIsUsed()
    {
        $mixable_class = new MixableTestClass();
        $mixable_class::mix('test', function () {
            return 'first value';
        });
        $mixable_class::mix('test', function () {
            return 'second value';
        });
        $this->assertEquals('second value', $mixable_class->test());
    }

    /**
     * Test that when multiple functions are mixed into a class with the same name,
     * the last function mixed in has its context used called on the mixable class
     *
     * @expectedException \Error
     */
    public function testAddingMultipleFunctionsWithSameNameThatLastAddedFunctionsContextIsUsed()
    {
        $this->expectException(Error::class);

        $mixable_class = new MixableTestClass();
        $mixable_class::mix('test', function () {
        }, ContextClosure::PUBLIC);
        $mixable_class::mix('test', function () {
        }, ContextClosure::PROTECTED);

        $mixable_class->test();
    }
}
