<?php

namespace ElephantWrench\Test;

use ReflectionClass;
use ReflectionObject;
use PHPUnit\Framework\TestCase;

class ElephantWrenchBaseTestCase extends TestCase
{
    /**
     * Global objects that PHPUnit should not try to make a backup of,
     * `applicationAspectKernel` is included here becuase it contains
     * closures which are unserializable and will cause PHPUnit to fail
     * if not black listed.
     *
     * @var array
     */
    protected $backupGlobalsBlacklist = array('applicationAspectKernel');

    /**
     * Classes whose static variables should be reset between every test
     * (See `ElephantWrenchBaseTestCase::setUpBeforeClass()` and `ElephantWrenchBaseTestCase::setUp()`)
     *
     * @var array
     */
    protected static $reset_classes = array();

    /**
     * Used to store the inital static properties of all classes
     * in the `ElephantWrenchBaseTestCase::$reset_classes` static property
     *
     * @var array
     */
    private static $saved_static_properties = array();

    /**
     * Run once before all tests in a class, create a backup of all the inital
     * static properties for classes in the `ElephantWrenchBaseTestCase::$reset_classes`
     * static property. These will be reset before every test.
     */
    public static function setUpBeforeClass()
    {
        foreach (static::$reset_classes as $reset_class)
        {
            static::saveStaticProperties($reset_class);
        }
    }

    /**
     * Run once before every test. Restore all static properties of classes in
     * the `ElephantWrenchBaseTestCase::$reset_classes` static property to
     * their original values.
     */
    public function setUp()
    {
        foreach (static::$reset_classes as $reset_class)
        {
            static::restoreStaticProperties($reset_class);
        }
    }

    /**
     * Assert that two arrays are similar; similar arrays are arrays that
     * have all the same key value pairs. Order is not checked but nested
     * arrays are checked that they are also similar.
     *
     * @param  array  $expected
     * @param  array  $actual
     */
    protected function assertArraysSimilar(array $expected, array $actual)
    {
        $this->assertCount(count($expected), $actual);
        foreach ($expected as $expected_key => $expected_value)
        {
            $this->assertArrayHasKey($expected_key, $actual);
            if (is_array($actual[$expected_key]))
            {
                $this->assertArraysSimilar($expected_value, $actual[$expected_key]);
            }
            else
            {
                $this->assertEquals($expected_value, $actual[$expected_key]);
            }
        }
    }

    /**
     * Return the value of a non public property for an object,
     * useful for testing. Does work with public functions.
     *
     * @param  object $object        Object that we want the value from
     * @param  string $property_name Name of the property that we want
     *
     * @return mixed
     */
    protected function getNonPublicProperty(object $object, string $property_name)
    {
        $reflection_class = new ReflectionObject($object);
        $property = $reflection_class->getProperty($property_name);
        $property->setAccessible(True);
        return $property->getValue($object);
    }

    /**
     * Save the static properties of a class
     *
     * @param  string $class
     */
    final protected static function saveStaticProperties(string $class)
    {
        $reflection_class = new ReflectionClass($class);
        self::$saved_static_properties[$class] = $reflection_class->getStaticProperties();
    }

    /**
     * Restore the static properties of a class that has previously been saved
     *
     * @param  string $class
     *
     * @throws Exception     Thown when trying to restore a class that hasn't
     *                       been previously saved with the `static::saveStaticProperties`
     *                       function
     */
    final protected static function restoreStaticProperties($class)
    {
        if (!isset(self::$saved_static_properties))
        {
            throw new Exception('Trying to restore a class that has not been saved!');
        }

        $reflection_class = new ReflectionClass($class);
        foreach (self::$saved_static_properties[$class] as $property_name => $property_value)
        {
            $property = $reflection_class->getProperty($property_name);
            $property->setAccessible(True);
            $property->setValue($property_value);
        }
    }

    // public function testPhpunitLoads() {
    //     $this->assertTrue(True);
    // }
}
