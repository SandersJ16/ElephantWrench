<?php

namespace ElephantWrench\Test\Helpers;

class MixinClass
{
    public $public_mixin_property = 'public mixin property';
    protected $protected_mixin_property = 'protected mixin property';
    private $private_mixin_property = 'private mixin property';

    public function publicMethod() {}
}
