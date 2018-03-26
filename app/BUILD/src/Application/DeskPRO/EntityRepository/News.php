<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Person as PersonEntity;
use Orb\Util\Strings;

class News extends AbstractEntityRepository
{
    public function getBySlug($slug)
    {
        $id = Strings::extractRegexMatch('#^([0-9]+)#', $slug, 1);
        if (!$id) {
            return;
        }

        return $this->find($id);
    }

    /**
     * Get a collection of posts by ID. If $person_context
     * is supplied, only articles that this person is able to view will be returned.
     *
     * @return array
     */
    public function getByIdsWithContext(array $ids, PersonEntity $person_context = null)
    {
        if (!$ids) {
            return [];
        }

        if ($person_context) {
            $cat_ids = $person_context->getPermissionsManager()->NewsCategories->getAllowedCategories();
            if (!$cat_ids) {
                return [];
            }

            $posts = $this->getEntityManager()->createQuery("
                SELECT p
                FROM DeskPRO:News p INDEX BY p.id
                WHERE p.id IN (?0) AND p.category IN (?1) AND p.status = 'published'
                ORDER BY p.id DESC
            ")->execute([$ids, $cat_ids]);
        } else {
            $posts = $this->getEntityManager()->createQuery("
                SELECT p
                FROM DeskPRO:News p INDEX BY p.id
                WHERE p.id IN (?0) AND p.status = 'published'
                ORDER BY p.id DESC
            ")->execute([$ids]);
        }

        return $posts;
    }

    public function getByResultIds(array $ids)
    {
        if (!$ids) {
            return [];
        }

        $unsorted_news = $this->getEntityManager()->createQuery('
            SELECT n
            FROM DeskPRO:News n INDEX BY n.id
            WHERE n.id IN (?0)
            ORDER BY n.id DESC
        ')->execute([$ids]);

        $news = [];

        foreach ($ids as $id) {
            if (isset($unsorted_news[$id])) {
                $news[$id] = $unsorted_news[$id];
            }
        }

        return $news;
    }

    public function getNews($node, $num = 20)
    {
        if ($node) {
            $news = $this->getEntityManager()->createQuery('
                SELECT n
                FROM DeskPRO:News n
                WHERE n.category = ?1
                ORDER BY n.id DESC
            ')->setParameter(1, $node)->setMaxResults($num)->execute();
        } else {
            $news = $this->getEntityManager()->createQuery('
                SELECT n
                FROM DeskPRO:News n
                ORDER BY n.id DESC
            ')->setMaxResults($num)->execute();
        }

        return $news;
    }

    public function getNewest($num = 10, $node = false)
    {
        if ($node) {
            $cat_ids  = $node->getTreeIds(true);
            $articles = $this->getEntityManager()->createQuery("
                SELECT n
                FROM DeskPRO:News n INDEX BY n.id
                WHERE n.status = 'published' AND n.category IN (?0)
                ORDER BY n.id DESC
            ")->setMaxResults($num)->execute([$cat_ids]);
        } else {
            $articles = $this->getEntityManager()->createQuery("
                SELECT n
                FROM DeskPRO:News n INDEX BY n.id
                WHERE n.status = 'published'
                ORDER BY n.id DESC
            ")->setMaxResults($num)->execute();
        }

        return $articles;
    }

    public function countPublished()
    {
        return $this->getEntityManager()->createQuery("
            SELECT COUNT(n) as cc
            FROM DeskPRO:News n
            WHERE n.status = 'published'
        ")->getSingleScalarResult();
    }

    public function getReportAssociations()
    {
        return [
            'views' => [
                'conditions'   => '%1$s.page_type = "deskpro.news_view" AND %1$s.page_id = %2$s.id',
                'targetEntity' => 'DeskPRO\\Bundle\\AppBundle\\Entity\\HitRecord',
            ],
        ];
    }
}
