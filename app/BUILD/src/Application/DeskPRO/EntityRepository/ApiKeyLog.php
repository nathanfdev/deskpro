<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

class ApiKeyLog extends AbstractEntityRepository
{
    const LIMIT = 50;

    /**
     * clean old records.
     */
    public function cleanup()
    {
        $limit = self::LIMIT;

        $key_ids = App::$container->getDb()->fetchAllCol('
            SELECT key_id
            FROM api_key_log
            GROUP BY key_id
            HAVING COUNT(*) > ?
        ', [$limit], [\PDO::PARAM_INT]);

        if (!$key_ids) {
            return 0;
        }

        foreach ($key_ids as $key_id) {
            $lid = App::$container->getDb()->fetchColumn("
                SELECT id
                FROM api_key_log
                WHERE key_id = ?
                ORDER BY id DESC
                LIMIT $limit, 1
            ", [$key_id]);

            if ($lid) {
                App::$container->getDb()->executeUpdate('
                    DELETE FROM api_key_log
                    WHERE key_id = ? AND id <= ?
                ', [$key_id, $lid]);
            }
        }
    }

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
