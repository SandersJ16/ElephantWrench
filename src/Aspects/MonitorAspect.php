<?php

namespace ElephantWrench\Aspects;

use Go\Aop\Aspect;
use Go\Aop\Intercept\FieldAccess;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\After;
use Go\Lang\Annotation\Before;
use Go\Lang\Annotation\Around;
use Go\Lang\Annotation\Pointcut;

/**
 * Monitor aspect
 */
class MonitorAspect implements Aspect
{
    /**
     * Method that will be called before real method
     *
     * @param MethodInvocation $invocation Invocation
     * @After("execution(public Test\Example->*(*))")
     */
    public function afterMethodExecution(MethodInvocation $invocation)
    {
        $obj = $invocation->getThis();
        echo 'Calling Before Interceptor for method: ',
        is_object($obj) ? get_class($obj) : $obj,
        $invocation->getMethod()->isStatic() ? '::' : '->',
        $invocation->getMethod()->getName(),
        '()',
        ' with arguments: ',
        json_encode($invocation->getArguments()),
        "<br>\n";
    }

    /**
     * Cacheable methods
     *
     * @param MethodInvocation $invocation Invocation
     *
     * @Before("@execution(Annotation\Cacheable)")
     */
    public function beforeCacheable(MethodInvocation $invocation)
    {
        echo 'Caching' . PHP_EOL;
        //echo "Caching $invocation->getMethod()->getName()" . PHP_EOL;
    }
}
