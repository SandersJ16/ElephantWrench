<?php

namespace ElephantWrench\Core\Util;

use ElephantWrench\Core\Exception\ContextCallableException;

class ContextCallable
{
    public const PUBLIC = 256;
    public const PROTECTED = 512;
    public const PRIVATE = 1024;

    protected $context;
    protected $callable;

    public function __construct(callable $callable, $context = self::PUBLIC)
    {
        $this->callable = $callable;
        $this->setContext($context);
    }

    public function getCallable()
    {
        return $this->callable;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function setContext($context)
    {
        if(!in_array($context, array(self::PUBLIC, self::PROTECTED, self::PRIVATE))) {
            throw new ContextCallableException("Context must be on of {self::class}::PUBLIC, {self::class}::PROTECTED, {self::class}::PRIVATE");
        }
        $this->context = $context;
    }

    public function isPublic()
    {
        return $this->context === self::PUBLIC;
    }

    public function isProtected()
    {
        return $this->context === self::PROTECTED;
    }

    public function isPrivate()
    {
        return $this->context === self::PRIVATE;
    }
}
