<?php

namespace DeskPRO\Bundle\AuditBundle\Storage\DB;

use DeskPRO\Bundle\AuditBundle\Entity\AuditLog as AuditLogEntity;
use DeskPRO\Bundle\AuditBundle\Storage\AbstractTransformer;

/**
 * Class DBTransformer.
 */
class DBTransformer extends AbstractTransformer
{
    /**
     * @return AuditLogEntity
     */
    protected function createAuditLog()
    {
        return new AuditLogEntity();
    }
}
