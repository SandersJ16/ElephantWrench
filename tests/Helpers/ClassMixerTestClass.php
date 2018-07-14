<?php

namespace ElephantWrench\Test\Helpers;

class ClassMixerTestClass
{
    public function returnHelloWorldWithOpenBracketOnNewLine()
    {
        return 'hello world';
    }

    public function returnHelloWorldWithOpenBracketOnSameLine() {
        return 'hello world';
    }

    public function returnHelloWorldDefinedOnOneLine() { return 'hello world'; }
}
