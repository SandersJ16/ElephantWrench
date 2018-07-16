<?php

namespace ElephantWrench\Test\Helpers;

class MixinClass
{
    public $public_mixin_property = 'public mixin property';
    protected $protected_mixin_property = 'protected mixin property';
    private $private_mixin_property = 'private mixin property';

    public function publicMethod()
    {
        return 'public method';
    }

    protected function protectedMethod()
    {
        return 'protected method';
    }

    private function privateMethod()
    {
        return 'private method';
    }

    public function publicMethodCallProtectedMethod()
    {
        return $this->protectedMethod();
    }

    public function publicMethodCallPrivateMethod()
    {
        return $this->privateMethod();
    }

    public function publicMethodReturnProtectedProperty()
    {
        return $this->protected_mixin_property;
    }

    public function publicMethodReturnPrivateProperty()
    {
        return $this->private_mixin_property;
    }

    public function publicMethodThatSetsProtectedProperty($value)
    {
        $this->protected_mixin_property = $value;
    }
}
