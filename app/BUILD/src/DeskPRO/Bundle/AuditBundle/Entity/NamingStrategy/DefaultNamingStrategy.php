<?php

namespace DeskPRO\Bundle\AuditBundle\Entity\NamingStrategy;

use DeskPRO\Bundle\AuditBundle\Log\AuditLog;

/**
 * Class DefaultNamingStrategy.
 */
class DefaultNamingStrategy implements NamingStrategyInterface
{
    /**
     * @param          $object
     * @param AuditLog $log
     *
     * @return string
     */
    public function getName($object, AuditLog $log)
    {
        switch (true) {
            case method_exists($object, 'getDisplayName'):
                return $object->getDisplayName();
            case method_exists($object, 'getName'):
                return $object->getName();
            case method_exists($object, 'getTitle'):
                return $object->getTitle();
            default:
                return sprintf('%s-%s', $log->getObjectType(), $log->getObjectId());
        }
    }
}
