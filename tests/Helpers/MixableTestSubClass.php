<?php

namespace ElephantWrench\Test\Helpers;

class MixableTestSubClass extends MixableTestClass
{
    public static $static_public_property = 'child static public';
    protected static $static_protected_property = 'child static protected';
    private static $static_private_property = 'child static private';
}
