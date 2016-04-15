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

/**
 * DeskPRO.
 */
namespace DeskPRO\Component\Util;

/**
 * Utility methods working with types.
 */
class TypeUtils
{
    /**
     * @param mixed $var
     *
     * @return string
     */
    public static function getVarType($var)
    {
        if (is_object($var)) {
            return get_class($var);
        } else {
            return gettype($var);
        }
    }

    /**
     * @param mixed $var
     *
     * @return string[]
     */
    public static function getTypeNameParts($var)
    {
        if (is_object($var)) {
            $var = get_class($var);
        }

        $parts = explode('\\', $var);

        return $parts;
    }

    /**
     * @param mixed $var
     *
     * @return string
     */
    public static function getBaseTypeName($var)
    {
        $parts = self::getTypeNameParts($var);

        return array_pop($parts);
    }

    /**
     * @param object $var
     *
     * @return string
     */
    public static function getSnakeCaseBaseTypeName($var)
    {
        return StringUtils::toSnakeCase(self::getBaseTypeName($var));
    }
}
