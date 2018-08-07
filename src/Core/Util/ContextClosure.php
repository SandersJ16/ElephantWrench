<?php

namespace ElephantWrench\Core\Util;

use Closure;

use ElephantWrench\Core\Exception\ContextCallableException;
use ElephantWrench\Core\Util\ClassMixer;

/**
 * This class is to be used as a wrapper around a closure,
 * using this instead of a closure directly lets you keep track
 * of properties about this closure. The context of the closure
 * is currently easily trackable but other properties can be added
 * to instances of this class unlike instances of closures
 */
class ContextClosure
{
    public const PUBLIC = 256;
    public const PROTECTED = 512;
    public const PRIVATE = 1024;

    protected $closure;
    protected $context;
    protected $static;

    /**
     * Create a new context closure
     *
     * @param  Closure $closure         The actual closure object this is a wrapper around
     * @param  int     $context         The context of this closure, can be Public, Protected or Private,
     *                                  needs to be one of the corresponding constants of this class
     * @param  bool    $static          Whether this closure should be handled as if it were static
     *
     * @throws ContextCallableException Thrown when trying to create a static ContextClosure with a closure
     *                                  that has instance context (uses $this variable)
     */
    public function __construct(Closure $closure, $context = self::PUBLIC, bool $static = false)
    {
        $this->static = $static;
        $this->closure = $closure;
        $this->setContext($context);

        if ($this->isStatic() && ClassMixer::hasInstanceContext($closure)) {
            throw new ContextCallableException('Can not create a static contextCallable using $this when not in object context');
        }
    }

    /**
     * Make this class directly invokable, it will just call the related closure
     *
     * @param  mixed $parameters Parameters to pass to the closure
     *
     * @return mixed             Return value of the closure
     */
    public function __invoke(...$parameters)
    {
        $closure = $this->getClosure();
        return $closure(...$parameters);
    }

    /**
     * Returns the closure of this class
     *
     * @return closure
     */
    public function getClosure() : Closure
    {
        return $this->closure;
    }

    /**
     * Return the context of this ContextClosure
     *
     * @return int
     */
    public function getContext() : int
    {
        return $this->context;
    }

    /**
     * Set the context of this ContextClosure
     *
     * @param int $context The context of this closure, can be Public, Protected or Private;
     *                     needs to be one of the corresponding constants of this class
     */
    public function setContext($context)
    {
        if (!in_array($context, array(self::PUBLIC, self::PROTECTED, self::PRIVATE))) {
            throw new ContextCallableException("Context must be on of {self::class}::PUBLIC, {self::class}::PROTECTED, {self::class}::PRIVATE");
        }
        $this->context = $context;
    }

    /**
     * Returns if the context of this closure is public
     *
     * @return boolean
     */
    public function isPublic() : bool
    {
        return $this->context === self::PUBLIC;
    }

    /**
     * Returns if the context of this closure is protected
     *
     * @return boolean
     */
    public function isProtected() : bool
    {
        return $this->context === self::PROTECTED;
    }

    /**
     * Returns if the context of this closure is private
     *
     * @return boolean
     */
    public function isPrivate() : bool
    {
        return $this->context === self::PRIVATE;
    }

    /**
     * Returns if the context of this closure is static
     *
     * @return boolean
     */
    public function isStatic() : bool
    {
        return $this->static;
    }
}
