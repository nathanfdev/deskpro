<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
}
