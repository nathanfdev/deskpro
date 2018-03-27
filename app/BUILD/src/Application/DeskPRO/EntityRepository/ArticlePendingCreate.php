<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Orb\Util\Arrays;

class ArticlePendingCreate extends AbstractEntityRepository
{
    public function getPendingArticles()
    {
        $pending_articles = $this->getEntityManager()->createQuery('
            SELECT a, t, p
            FROM DeskPRO:ArticlePendingCreate a
            LEFT JOIN a.ticket t
            LEFT JOIN a.person p
            ORDER BY a.date_created DESC
        ')->execute();

        return $pending_articles;
    }

    public function getByIds(array $ids, $keep_order = false)
    {
        $ids = Arrays::castToType($ids, 'int');
        $ids = Arrays::removeFalsey($ids);

        if (!$ids) {
            return [];
        }
        $ids = implode(',', $ids);

        return $this->getEntityManager()->createQuery("
            SELECT a
            FROM DeskPRO:ArticlePendingCreate a INDEX BY a.id
            WHERE a.id IN ($ids)
        ")->execute();
    }
}
