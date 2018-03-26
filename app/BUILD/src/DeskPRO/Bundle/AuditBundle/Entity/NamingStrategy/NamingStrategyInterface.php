<?php

namespace DeskPRO\Bundle\AuditBundle\Entity\NamingStrategy;

use DeskPRO\Bundle\AuditBundle\Log\AuditLog;

interface NamingStrategyInterface
{
    /**
     * @param          $object
     * @param AuditLog $log
     *
     * @return string
     */
    public function getName($object, AuditLog $log);
}
