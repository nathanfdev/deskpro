<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Person as PersonEntity;
use Orb\Util\Strings;

class Download extends AbstractEntityRepository
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
     * Get a collection of downloads by ID. If $person_context
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
            $cat_ids = $person_context->getPermissionsManager()->DownloadCategories->getAllowedCategories();
            if (!$cat_ids) {
                return [];
            }

            $downloads = $this->getEntityManager()->createQuery("
                SELECT d
                FROM DeskPRO:Download d INDEX BY d.id
                WHERE d.id IN (?0) AND d.category IN (?1) AND d.status = 'published'
                ORDER BY d.id DESC
            ")->execute([$ids, $cat_ids]);
        } else {
            $downloads = $this->getEntityManager()->createQuery('
                SELECT d
                FROM DeskPRO:Download d INDEX BY d.id
                WHERE d.id IN (?0)
                ORDER BY d.id DESC
            ')->execute([$ids]);
        }

        return $downloads;
    }

    public function getByResultIds(array $ids)
    {
        if (!$ids) {
            return [];
        }

        $unsorted_downloads = $this->getEntityManager()->createQuery('
            SELECT d
            FROM DeskPRO:Download d INDEX BY d.id
            WHERE d.id IN (?0)
            ORDER BY d.id DESC
        ')->execute([$ids]);

        $downloads = [];

        foreach ($ids as $id) {
            if (isset($unsorted_downloads[$id])) {
                $downloads[$id] = $unsorted_downloads[$id];
            }
        }

        return $downloads;
    }

    public function getNewest($num = 10, $node = false)
    {
        if ($node) {
            $downloads = $this->getEntityManager()->createQuery("
                SELECT d
                FROM DeskPRO:Download d
                WHERE d.category = ?1 AND d.status = 'published'
                ORDER BY d.id DESC
            ")->setParameter(1, $node)->setMaxResults($num)->execute();
        } else {
            $downloads = $this->getEntityManager()->createQuery("
                SELECT d
                FROM DeskPRO:Download d
                WHERE d.status = 'published'
                ORDER BY d.id DESC
            ")->setMaxResults($num)->execute();
        }

        return $downloads;
    }

    public function getPopular($num = 10, $node = false)
    {
        if ($node) {
            $downloads = $this->getEntityManager()->createQuery('
                SELECT d
                FROM DeskPRO:Download d
                WHERE d.category = ?1
                ORDER BY d.num_downloads DESC
            ')->setParameter(1, $node)->setMaxResults($num)->execute();
        } else {
            $downloads = $this->getEntityManager()->createQuery('
                SELECT d
                FROM DeskPRO:Download d
                ORDER BY d.num_downloads DESC
            ')->setMaxResults($num)->execute();
        }

        return $downloads;
    }

    public function getInNode($node)
    {
        return $this->getEntityManager()->createQuery('
            SELECT d
            FROM DeskPRO:Download d
            WHERE d.category = ?1
            ORDER BY d.title DESC
        ')->setParameter(1, $node)->execute();
    }

    public function getSectionCounts(PersonEntity $person_context = null)
    {
        $counts = [];

        $searcher = new \Application\DeskPRO\Searcher\DownloadSearch();
        if ($person_context) {
            $searcher->setPersonContext($person_context);
        }
        $searcher->addTerm('status', 'is', 'published');
        $searcher->addTerm('popular', 'is', '1');
        $counts['popular'] = $searcher->getCount();

        $searcher = new \Application\DeskPRO\Searcher\DownloadSearch();
        if ($person_context) {
            $searcher->setPersonContext($person_context);
        }
        $searcher->addTerm('status', 'is', 'published');
        $searcher->addTerm('new', 'is', '1');
        $counts['new'] = $searcher->getCount();

        return $counts;
    }

    public function countPublished()
    {
        return $this->getEntityManager()->createQuery(
            "
                        SELECT COUNT(n) as cc
                        FROM DeskPRO:Download n
                        WHERE n.status = 'published'
                    "
        )->getSingleScalarResult();
    }

    public function getReportAssociations()
    {
        return [
            'views' => [
                'conditions'   => '%1$s.page_type = "deskpro.download_view" AND %1$s.page_id = %2$s.id',
                'targetEntity' => 'DeskPRO\\Bundle\\AppBundle\\Entity\\HitRecord',
            ],
        ];
    }
}
