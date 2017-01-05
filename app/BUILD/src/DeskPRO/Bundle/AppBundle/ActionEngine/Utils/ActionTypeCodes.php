<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\ActionEngine\Utils;

use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\ActionInterface;
use DeskPRO\Bundle\AppBundle\ActionEngine\Exception\ActionApplicatorDoesNotExists;
use Orb\Util\Strings;

/**
 * A static utility class that allows you to map action type code into applicator class name.
 *
 * Type code: feedback:approve
 * Class: DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\Feedback\ApplyApproveAction
 */
class ActionTypeCodes
{
    /**
     * Remove namespace and remove "Action" from the end of the class name, lowercase.
     *
     * example: DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Common\FooBarAction converts to: "foo_bar"
     *
     * @param ActionInterface $action
     *
     * @return string type code
     */
    public static function getActionName(ActionInterface $action)
    {
        return strtolower(
            Strings::camelCaseToUnderscore(
                substr(implode('', array_slice(explode('\\', get_class($action)), -1)), 0, -6)
            )
        );
    }

    /**
     * Example: ("feedback", "approve") to classname DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Common\ApproveAction.
     *
     * @param string $namespace
     * @param string $actionName
     *
     * @return string
     */
    public static function getActionClass($namespace, $actionName)
    {
        $actionClassName = ucfirst(Strings::underscoreToCamelCase($actionName));

        // Search action class in $namespace
        $actionClass = sprintf(
            'DeskPRO\\Bundle\\AppBundle\\ActionEngine\\Actions\\%s\\%sAction',
            $namespace,
            $actionClassName
        );
        // If not found - try to search in common actions
        if (!class_exists($actionClass)) {
            $actionClass = sprintf(
                'DeskPRO\\Bundle\\AppBundle\\ActionEngine\\Actions\\Common\\%sAction',
                $actionClassName
            );
            if (!class_exists($actionClass)) {
                throw new ActionApplicatorDoesNotExists("Action class '$actionName' doesn't exists");
            }
        }

        return $actionClass;
    }

    /**
     * Example: "approve" to classname: DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\Feedback\ApplyApproveAction.
     *
     * @param string $namespace
     * @param string $actionName
     *
     * @return string
     */
    public static function getActionApplicatorClassForActionName($namespace, $actionName)
    {
        $actionApplicatorClassName = ucfirst(Strings::underscoreToCamelCase($actionName));
        $actionApplicatorClass     = sprintf(
            'DeskPRO\\Bundle\\AppBundle\\ActionEngine\\Applicators\\%s\\Apply%sAction',
            $namespace,
            $actionApplicatorClassName
        );
        if (!class_exists($actionApplicatorClass)) {
            throw new ActionApplicatorDoesNotExists('Action Applicator Does Not Exists '.$actionName);
        }

        return $actionApplicatorClass;
    }
}
