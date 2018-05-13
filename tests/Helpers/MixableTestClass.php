<?php

namespace ElephantWrench\Test\Helpers;

use ElephantWrench\Core\Traits\Mixable;

class MixableTestClass
{
    use Mixable;

    public $public_property = 'public';
    protected $protected_property = 'protected';
    private $private_property = 'private';
}
