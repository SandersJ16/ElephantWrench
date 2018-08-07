<?php

namespace ElephantWrench\Test\Helpers;

use ElephantWrench\Core\Traits\Mixable;

class MixableTestClass
{
    use Mixable;

    public $public_property = 'public';
    protected $protected_property = 'protected';
    private $private_property = 'private';

    public static $static_public_property = 'static public';
    protected static $static_protected_property = 'static protected';
    private static $static_private_property = 'static private';

    public function publicNonMixedMethod()
    {
        return 'public NonMixed Method';
    }

    protected function protectedNonMixedMethod()
    {
        return 'protected NonMixed Method';
    }

    private function privateNonMixedMethod()
    {
        return 'private NonMixed Method';
    }

    public static function publicNonMixedStaticMethod()
    {
        return 'public NonMixed Static Method';
    }

    protected static function protectedNonMixedStaticMethod()
    {
        return 'protected NonMixed Static Method';
    }

    private static function privateNonMixedStaticMethod()
    {
        return 'private NonMixed Static Method';
    }
}
