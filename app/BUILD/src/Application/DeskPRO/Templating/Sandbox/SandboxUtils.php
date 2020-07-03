<?php

namespace Application\DeskPRO\Templating\Sandbox;

/**
 * Class SandboxUtils
 *
 * @package Application\DeskPRO\Templating\Sandbox
 */
class SandboxUtils
{
    public static function guessAccessors($class, $extra = [], $methodPrefixes = ['get', 'is', 'has', 'can'])
    {
        return array_merge($extra, array_filter(get_class_methods($class), function ($method) use ($methodPrefixes) {
            return preg_match(sprintf('/^(%s)/', implode('|', $methodPrefixes)), $method);
        }));
    }
}
