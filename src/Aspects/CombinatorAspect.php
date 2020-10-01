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
class CombinatorAspect implements Aspect
{
    /**
     * Called before all functions to check if it is a Combinator call
     *
     * @param  MethodInvocation $incovation [description]
     *
     * @return mixed
     *
     * @Around("execution(public|protected *->*(*))")
     */
    public function beforeCombinatorCall(MethodInvocation $incovation)
    {
        print "hello World" . PHP_EOL;
    }

    /**
     * Called before all functions to check if it is a Combinator call
     *
     * @param  MethodInvocation $incovation [description]
     *
     * @return mixed
     *
     * @Around("execution(public|protected *->*(*))")
     */
    public function testbeforeCombinatorCall(MethodInvocation $incovation)
    {
        error_log('test');
    }
}
