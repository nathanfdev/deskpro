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
