<?php

namespace ElephantWrench\Test;

use ElephantWrench\Core\Util\ClassMixer;
use ElephantWrench\Test\Helpers\ClassMixerTestClass;

/**
 * This class tests the ClassMixer Static Class
 */
class ClassMixerTest extends ElephantWrenchBaseTestCase
{
    public function testMethodToRealClosure()
    {
        $hello_world_closure = ClassMixer::classMethodToRealClosure(ClassMixerTestClass::class, 'returnHelloWorld');
        $this->assertEquals('hello world', $hello_world_closure());
    }
}
