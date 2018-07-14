<?php

namespace ElephantWrench\Test;

use ElephantWrench\Core\Util\ClassMixer;
use ElephantWrench\Test\Helpers\ClassMixerTestClass;

/**
 * This class tests the ClassMixer Static Class
 */
class ClassMixerTest extends ElephantWrenchBaseTestCase
{
    // public function testMethodToRealClosureWithOpenBracketOnNewLine()
    // {
    //     $hello_world_closure = ClassMixer::classMethodToRealClosure(ClassMixerTestClass::class, 'returnHelloWorldWithOpenBracketOnNewLine');
    //     $this->assertEquals('hello world', $hello_world_closure());
    // }

    // public function testMethodToRealClosureWithOpenBracketOnSameLine()
    // {
    //     $hello_world_closure = ClassMixer::classMethodToRealClosure(ClassMixerTestClass::class, 'returnHelloWorldWithOpenBracketOnSameLine');
    //     $this->assertEquals('hello world', $hello_world_closure());
    // }

    /**
     * @dataProvider hellowWorldMethodNameProvider
     */
    public function testReturnHelloWorldFunctions($method_name)
    {
        $hello_world_closure = ClassMixer::classMethodToRealClosure(ClassMixerTestClass::class, $method_name);
        $this->assertEquals('hello world', $hello_world_closure());
    }

    public function hellowWorldMethodNameProvider()
    {
        return array(['returnHelloWorldWithOpenBracketOnNewLine'],
                     ['returnHelloWorldWithOpenBracketOnSameLine'],
                     ['returnHelloWorldDefinedOnOneLine'],
                     ['returnHelloWorldDefinedOnMultiLinesButStartsOnLineOne']);
    }
}
