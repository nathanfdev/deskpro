<?php

namespace DeskPRO\Bundle\AuditBundle\Storage\MongoDB;

use DeskPRO\Bundle\AuditBundle\Document\AuditLog as AuditLogDocument;
use DeskPRO\Bundle\AuditBundle\Storage\AbstractTransformer;

/**
 * Class MongoDBTransformer.
 */
class MongoDBTransformer extends AbstractTransformer
{
    /**
     * @return AuditLogDocument
     */
    protected function createAuditLog()
    {
        return new AuditLogDocument();
    }
}
