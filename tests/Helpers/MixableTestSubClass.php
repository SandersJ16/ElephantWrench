<?php

namespace ElephantWrench\Test\Helpers;

class MixableTestSubClass extends MixableTestClass
{
    public $public_property = 'child public';
    protected $protected_property = 'child protected';

    public static $static_public_property = 'child static public';
    protected static $static_protected_property = 'child static protected';

    public function publicNonMixedMethod()
    {
        return 'child ' . parent::publicNonMixedMethod();
    }

    protected function protectedNonMixedMethod()
    {
        return 'child ' . parent::protectedNonMixedMethod();
    }

    public static function publicNonMixedStaticMethod()
    {
        return 'child ' . parent::publicNonMixedStaticMethod();
    }

    protected static function protectedNonMixedStaticMethod()
    {
        return 'child ' . parent::protectedNonMixedStaticMethod();
    }
}
