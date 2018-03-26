<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\Util;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;

/**
 * General utilities for working with the DeskPRO\Bundle\AppBundle\EntityInterface.
 */
class EntityUtils
{
    /**
     * @param EntityInterface|DomainObject $entity
     *
     * @return string|int
     */
    public static function getIdentifier($entity)
    {
        if (!($entity instanceof EntityInterface) && !($entity instanceof DomainObject)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'EntityUtils::getIdentifier only supports instances of "%s" or "%s"',
                    EntityInterface::class,
                    DomainObject::class
                )
            );
        }

        $id = $entity->getId();

        if (!self::isNullIdValue($id)) {
            if (is_scalar($id)) {
                return $id;
            } elseif (is_array($id)) {
                return implode('|', $id);
            } else {
                throw new \Exception('Logic error in EntityUtils::getIdentifier. This should be impossible to reach. The ID given is not supported in this iteration of EntityUtils::getIdentifier, and needs to be updated to support it.');
            }
        }

        return;
    }

    protected static function isNullIdValue($id)
    {
        $empty_or_bool = is_bool($id) || empty($id);

        if ($empty_or_bool && $id !== 0) {
            return true;
        }

        if (is_array($id)) {
            foreach ($id as $array_val) {
                if (!self::isNullIdValue($array_val)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param $id
     *
     * @return string
     */
    protected static function parseIdForException($id)
    {
        try {
            return (string) $id;
        } catch (\Exception $e) {
            try {
                return json_encode($id);
            } catch (\Exception $e) {
            }
        }

        return 'cannot parse';
    }
}
