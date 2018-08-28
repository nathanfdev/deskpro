<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity;

class ApiKeyLog extends AbstractEntityRepository
{
    /**
     * @param Entity\ApiKey $api_key
     * @param int           $limit
     *
     * @return Entity\ApiKeyLog[]
     */
    public function getLogsForKey(Entity\ApiKey $api_key, $limit = 100)
    {
        return $this->_em->createQuery('
            SELECT l
            FROM DeskPRO:ApiKeyLog l
            WHERE l.key = ?0
            ORDER BY l.id DESC
        ')->setMaxResults($limit)->execute([$api_key]);
    }
}
