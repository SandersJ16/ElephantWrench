<?php

namespace ElephantWrench\Core\Util;

use ElephantWrench\Core\Exception\ContextCallableException;

/**
 * This class is to be used as a wrapper around a callable,
 * using this instead of a callable directly lets you keep track
 * of properties about this callable. The context of the callable
 * is currently easily trackable but other properties can be added
 * to instances of this class unlike Closures and Callables
 */
class ContextCallable
{
    public const PUBLIC = 256;
    public const PROTECTED = 512;
    public const PRIVATE = 1024;

    protected $callable;
    protected $context;

    /**
     * Create a new context callable
     *
     * @param callable $callable The actual callable object this is a wrapper around
     * @param int      $context  The context of this callable, can be Public, Protected or Private;
     *                           needs to be one of the corresponding constants of this class
     */
    public function __construct(callable $callable, $context = self::PUBLIC)
    {
        $this->callable = $callable;
        $this->setContext($context);
    }

    /**
     * Returns the callable of this class
     *
     * @return callable
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * Return the context of this ContextCallable
     *
     * @return int
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set the context of this ContextCallable
     *
     * @param int $context The context of this callable, can be Public, Protected or Private;
     *                     needs to be one of the corresponding constants of this class
     */
    public function setContext($context)
    {
        if(!in_array($context, array(self::PUBLIC, self::PROTECTED, self::PRIVATE))) {
            throw new ContextCallableException("Context must be on of {self::class}::PUBLIC, {self::class}::PROTECTED, {self::class}::PRIVATE");
        }
        $this->context = $context;
    }

    /**
     * Returns if the context of this callable is public
     *
     * @return boolean
     */
    public function isPublic()
    {
        return $this->context === self::PUBLIC;
    }

    /**
     * Returns if the context of this callable is protected
     *
     * @return boolean
     */
    public function isProtected()
    {
        return $this->context === self::PROTECTED;
    }

    /**
     * Returns if the context of this callable is private
     *
     * @return boolean
     */
    public function isPrivate()
    {
        return $this->context === self::PRIVATE;
    }
}
