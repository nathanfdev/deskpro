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

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use Doctrine\Common\Util\ClassUtils;

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

    /**
     * Check if $value is an integer or a string that is an integer.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function isIntLike($value)
    {
        if (!is_scalar($value) or is_array($value)) {
            return false;
        }

        if (is_int($value) or ((string) ((int) $value)) == (string) $value) {
            return true;
        }

        return false;
    }

    /**
     * Checks if a value can be iterated over with foreach.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function isTraversable($value)
    {
        return is_array($value) || $value instanceof \Traversable;
    }

    /**
     * Checks if you can use array notation on a variable.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function isArrayLike($value)
    {
        return is_array($value) || $value instanceof \ArrayAccess;
    }

    /**
     * Checks if a value is a list (aka numerically indexed array).
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function isList($value)
    {
        if (!self::isArrayLike($value) || !self::isTraversable($value)) {
            return false;
        }

        if (
            $value instanceof \SplFixedArray
            || $value instanceof \SplStack
            || $value instanceof \SplQueue
            || $value instanceof \SplDoublyLinkedList
        ) {
            return true;
        }

        $idx = 0;
        foreach ($value as $k => $v) {
            if ($k !== $idx++) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param EntityInterface|DomainObject $entity
     *
     * @return string
     */
    public static function getEntityClass($entity)
    {
        if (!$entity instanceof EntityInterface && !$entity instanceof DomainObject) {
            throw new \InvalidArgumentException(
                sprintf('Objects with type [ %s ] are not supported', get_class($entity))
            );
        }

        return ClassUtils::getRealClass(get_class($entity));
    }
}
