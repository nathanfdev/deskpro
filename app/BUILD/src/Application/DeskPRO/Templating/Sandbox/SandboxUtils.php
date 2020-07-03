<?php

namespace Application\DeskPRO\Templating\Sandbox;

/**
 * Class SandboxUtils
 *
 * @package Application\DeskPRO\Templating\Sandbox
 */
class SandboxUtils
{
    /**
     * Try to guess a list of accessors for a given object
     *
     * @param string   $class Object's class name
     * @param string[] $extra Extra method names to add to the returned list
     * @param string[] $methodPrefixes List of accessor method prefixes to look for
     * @return array
     */
    public static function guessAccessors($class, $extra = [], $methodPrefixes = ['get', 'is', 'has', 'can'])
    {
        return array_merge($extra, array_filter(get_class_methods($class), function ($method) use ($methodPrefixes) {
            return preg_match(sprintf('/^(%s)/', implode('|', $methodPrefixes)), $method);
        }));
    }
}
