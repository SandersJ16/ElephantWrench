<?php

namespace ElephantWrench\Test;

use ReflectionClass;
use PHPUnit\Framework\TestCase;

class ElephantWrenchBaseTestCase extends TestCase
{
    protected $backupGlobalsBlacklist = array('applicationAspectKernel');

    protected static $reset_classes = array();
    private static $saved_static_properties = array();

    public static function setUpBeforeClass()
    {
        foreach (static::$reset_classes as $reset_class)
        {
            static::saveStaticProperties($reset_class);
        }
    }

    public function setUp()
    {
        foreach (static::$reset_classes as $reset_class)
        {
            static::restoreStaticProperties($reset_class);
        }
    }

    public function assertArraysSimilar(array $expected, array $actual)
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
                $this->assertSame($expected_value, $actual[$expected_key]);
            }
        }
    }

    final protected static function saveStaticProperties($class)
    {
        $reflection_class = new ReflectionClass($class);
        self::$saved_static_properties[$class] = $reflection_class->getStaticProperties();
    }

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
            $property->setAccessible(true);
            $property->setValue($property_value);
        }
    }

    // public function testPhpunitLoads() {
    //     $this->assertTrue(true);
    // }
}
