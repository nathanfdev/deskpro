<?php

namespace DeskPRO\Component\Util;

use Symfony\Component\Routing\Route;

/**
 * Class ControllerUtils.
 */
class ControllerUtils
{
    /**
     * @param string $action_name
     * @param bool   $remove_version
     *
     * @return mixed|string
     */
    public static function cleanAction($action_name, $remove_version = false)
    {
        // remove class name if __METHOD__ was passed
        if (strpos($action_name, '::')) {
            $action_name = explode('::', $action_name)[1];
        }

        if ($remove_version) {
            $action_name = preg_replace('#([a-zA-Z]+?)(\d+)(Action)#', '$1$3', $action_name);
        }

        // remove 'Action' postfix to get the short action name in case if __METHOD__ or __FUNCTION__ is passed
        $action = strpos($action_name, 'Action') === strlen($action_name) - strlen('Action')
            ? substr($action_name, 0, strlen($action_name) - strlen('Action'))
            : $action_name;

        return $action;
    }

    /**
     * Extract \ReflectionClass from route default _controller attribute.
     *
     * @param Route $route
     *
     * @return \ReflectionClass|false
     */
    public static function extractControllerReflection(Route $route)
    {
        if ($class = self::extractControllerClass($route)) {
            return new \ReflectionClass($class);
        }

        return false;
    }

    /**
     * @param Route $route
     *
     * @return bool
     */
    public static function extractControllerClass(Route $route)
    {
        $ctrl          = $route->getDefault('_controller');
        $parts         = explode('::', $ctrl);
        $is_controller = preg_match('#^DeskPRO\\\\Bundle\\\\ApiBundle\\\\#', $ctrl);

        if ($is_controller && $parts[0]) {
            return $parts[0];
        }

        return false;
    }

    public static function calculateTag($className, $action)
    {
        $matches = [];
        if (preg_match_all('#(.*?\\Controller)#i', $className, $matches)) {
            $fqcn = ltrim($matches[0][1], '\\');
        } else {
            $fqcn = $className;
        }

        $tag   = explode('\\', $fqcn);
        $tag[] = substr($action, 0, -6);
        foreach ($tag as &$t) {
            $t = str_replace('Controller', '', $t);
            $t = StringUtils::toSnakeCase($t);
        }
        $tag = implode('.', $tag);

        return $tag;
    }
}
