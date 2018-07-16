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

    protected function protectedNonMixedMethod() {}
}
