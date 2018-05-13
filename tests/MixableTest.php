<?php

namespace ElephantWrench\Test;

use PHPUnit_Framework_Error_Notice;
use ElephantWrench\Test\Helpers\{MixableTestClass, MixableTestSubClass};

class TestMixable extends ElephantWrenchBaseTestCase
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
        $mixable_class::mix('getPublicProperty', function() {
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
        $mixable_class::mix('getProtectedProperty', function() {
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
        $mixable_class::mix('getPrivateProperty', function() {
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
        $mixable_class::mix('getPublicProperty', function() {
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
        $mixable_class::mix('getProtectedProperty', function() {
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
        $mixable_class::mix('getPrivateProperty', function() {
            return $this->private_property;
        });

        //Shouldn't thow an exception since the function was registered to a class that has access to this private property
        $this->assertEquals($this->getNonPublicProperty($mixable_class, 'private_property'), $mixable_subclass->getPrivateProperty());
    }

    /**
     * Test that a function added to the subclass of a Mixable can
     * NOT use a private non-static property defined on the Mixable
     */
    public function testAddingFunctionThatUsesParentExistingMixableClassPrivatePropertyFromSubclass()
    {
        //`Use of undefined constant` does not throw a Throwable so PHPUnit handles it
        //using `set_error_handler()` and then throws a PHPUnit_Framework_Error_Notice
        $this->expectException(PHPUnit_Framework_Error_Notice::class);

        $mixable_subclass = new MixableTestSubClass();
        $mixable_subclass::mix('getPrivateProperty', function() {
            return $this->private_property;
        });

        //Should throw an exception for undefined property `private_property`
        //since getPrivateProperty was added to `MixableTestSubClass` which can not access
        //the private properties of its parent `MixableTestClass`
        $mixable_subclass->getPrivateProperty();
    }

}
