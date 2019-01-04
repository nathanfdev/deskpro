<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person as PersonEntity;
use Doctrine\ORM\Query\Expr;
use Orb\Util\Numbers;
use Orb\Util\Strings;

class Feedback extends AbstractEntityRepository
{
    //###########################################################################
    // Counters
    //###########################################################################

    /**
     * Count the number of feedback that are awaiting validation.
     *
     * @return int
     */
    public function countAwaitingValidation()
    {
        return $this->getEntityManager()->getConnection()->fetchColumn('
            SELECT COUNT(*)
            FROM feedback
            WHERE is_reviewed = 0
        ');
    }

    public function getAwaitingValidation($limit, $offset = 0)
    {
        $qb = $this->createQueryBuilder('f');
        $qb
            ->where($qb->expr()->eq('f.is_reviewed', 0))
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('f.date_created', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Count the number of feedback that are 'active', grouped by status category as key.
     * The key 0 will be used as the total.
     *
     * @param int $brandId
     *
     * @return array
     */
    public function countActiveGrouped($brandId)
    {
        return $this->getEntityManager()->getConnection()->fetchAllKeyValue("
            SELECT IFNULL(status_category_id, 0), COUNT(*) as count
            FROM feedback
            WHERE status = 'active' AND brand_id = ?
            GROUP BY status_category_id WITH ROLLUP
        ", [$brandId]);
    }

    /**
     * Count the number of feedback that are 'active', grouped by status category as key.
     * The key 0 will be used as the total.
     *
     * @param int $brandId
     *
     * @return array
     */
    public function countClosedGrouped($brandId)
    {
        return $this->getEntityManager()->getConnection()->fetchAllKeyValue("
            SELECT IFNULL(status_category_id, 0), COUNT(*) as count
            FROM feedback
            WHERE status = 'closed' AND brand_id = ?
            GROUP BY status_category_id WITH ROLLUP
        ", [$brandId]);
    }

    /**
     * Count the number of hidden feedback, groupbed by hidden_status as key.
     * The key 'hidden' will be used as the total.
     *
     * @param int $brandId
     *
     * @return array
     */
    public function countHiddenGrouped($brandId)
    {
        // We dont count validating with this number because
        // in the UI we generally show validating separately
        return $this->getEntityManager()->getConnection()->fetchAllKeyValue("
            SELECT IFNULL(hidden_status, 'hidden'), COUNT(*) as count
            FROM feedback
            WHERE status = ? AND brand_id = ?
            GROUP BY hidden_status WITH ROLLUP
        ", ['hidden', $brandId]);
    }

    /**
     * Count the number of feedback that are new.
     *
     * @return int
     */
    public function countNew()
    {
        return $this->getEntityManager()->getConnection()->fetchColumn("
            SELECT COUNT(*)
            FROM feedback
            WHERE status = 'new'
        ");
    }

    /**
     * Count the number of non-hidden feedback in all categories, grouped by category ID key.
     * Each parent category has the sum of all children.
     *
     * @return array
     */
    public function countAllCategoriesGrouped()
    {
        /*
         * Note that the order by category_id ASC is important here.
         * The tally loop after modifies the array as we go. We cant
         * have a parents tally using a childs tally that was already incremented,
         * that'd result in incorrect tallies.
         * (Could just make a 2nd new array using 1st as a lookup, but this solution is easy enough)
         */

        $counts = $this->getEntityManager()->getConnection()->fetchAllKeyValue("
            SELECT category_id, COUNT(*)
            FROM feedback
            WHERE status != 'hidden'
            GROUP BY category_id
            ORDER BY category_id ASC
        ");

        foreach ($counts as $cat_id => &$count) {
            $cat_childs = App::getEntityRepository('DeskPRO:FeedbackCategory')->getIdsInTree($cat_id, false);
            if ($cat_childs) {
                foreach ($cat_childs as $child_cat_id) {
                    if (isset($counts[$child_cat_id])) {
                        $count += $counts[$child_cat_id];
                    }
                }
            }
        }

        return $counts;
    }

    /**
     * Count the number of feedback in a status category.
     *
     * @param $category
     *
     * @return int
     */
    public function countInCategory($category)
    {
        return $this->getEntityManager()->getConnection()->fetchColumn('
            SELECT COUNT(*)
            FROM feedback
            WHERE category_id = ?
        ', [$category->id]);
    }

    /**
     * Count the number of feedback in a status category.
     *
     * @param $category
     *
     * @return int
     */
    public function countInStatusCategory($category)
    {
        return $this->getEntityManager()->getConnection()->fetchColumn('
            SELECT COUNT(*)
            FROM feedback
            WHERE status_category_id = ?
        ', [$category->id]);
    }

    //###########################################################################
    // Fetchers
    //###########################################################################

    public function getBySlug($slug)
    {
        $id = Strings::extractRegexMatch('#^([0-9]+)#', $slug, 1);
        if (!$id) {
            return;
        }

        return $this->find($id);
    }

    /**
     * Get a collection of feedback by ID. If $person_context
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
            $feedback = $this->getEntityManager()->createQuery("
                SELECT i
                FROM DeskPRO:Feedback i INDEX BY i.id
                WHERE i.id IN (?0) AND i.status != 'hidden'
                ORDER BY i.id DESC
            ")->execute([$ids]);
        } else {
            $feedback = $this->getEntityManager()->createQuery('
                SELECT i
                FROM DeskPRO:Feedback i INDEX BY i.id
                WHERE i.id IN (?0)
                ORDER BY i.id DESC
            ')->execute([$ids]);
        }

        return $feedback;
    }

    public function getByResultIds(array $ids)
    {
        if (!$ids) {
            return [];
        }

        $unsorted_feedback = $this->getEntityManager()->createQuery('
            SELECT i
            FROM DeskPRO:Feedback i INDEX BY i.id
            WHERE i.id IN (?0)
            ORDER BY i.id DESC
        ')->execute([$ids]);

        $feedback = [];

        foreach ($ids as $id) {
            if (isset($unsorted_feedback[$id])) {
                $feedback[$id] = $unsorted_feedback[$id];
            }
        }

        return $feedback;
    }

    public function getFeedback($status, $node = false, $sort = 'id', $num = 10)
    {
        if ($sort == 'date') {
            $sort = 'id';
        }
        if (!in_array($sort, ['id', 'num_ratings'])) {
            $sort = 'id';
        }

        if ($node) {
            $node_ids = $node->getTreeIds(true);

            $feedback = $this->getEntityManager()->createQuery("
                SELECT i
                FROM DeskPRO:Feedback i
                WHERE i.category IN (?0) AND i.status = ?1
                ORDER BY i.$sort DESC
            ")->setMaxResults($num)->execute([$node_ids, $status]);
        } else {
            $feedback = $this->getEntityManager()->createQuery("
                SELECT i
                FROM DeskPRO:Feedback i
                WHERE i.status = ?0
                ORDER BY i.$sort DESC
            ")->setMaxResults($num)->execute([$status]);
        }

        return $feedback;
    }

    public function countNotClosedNotHidden()
    {
        return $this->getEntityManager()->createQuery(
            "
                        SELECT COUNT(n) as cc
                        FROM DeskPRO:Feedback n
                        WHERE n.status != 'closed' AND n.status != 'hidden'
                    "
        )->getSingleScalarResult();
    }

    public function getNewest($status, $num = 10, $node = false)
    {
        if (!$status) {
            $feedback = $this->getEntityManager()->createQuery("
                SELECT i
                FROM DeskPRO:Feedback i INDEX BY i.id
                WHERE i.status != 'closed' AND i.status != 'hidden'
                ORDER BY i.id DESC
            ")->setMaxResults($num)->execute();

            return $feedback;
        }

        if (Numbers::isInteger($status)) {
            if ($node) {
                $cat_ids  = $node->getTreeIds(true);
                $feedback = $this->getEntityManager()->createQuery('
                    SELECT i
                    FROM DeskPRO:Feedback i INDEX BY i.id
                    WHERE i.status_category = ?0 AND i.category IN (?1)
                    ORDER BY i.id DESC
                ')->setMaxResults($num)->execute([$status, $cat_ids]);
            } else {
                $feedback = $this->getEntityManager()->createQuery('
                    SELECT i
                    FROM DeskPRO:Feedback i INDEX BY i.id
                    WHERE i.status_category = ?0
                    ORDER BY i.id DESC
                ')->setMaxResults($num)->execute([$status]);
            }
        } else {
            if ($node) {
                $cat_ids  = $node->getTreeIds(true);
                $feedback = $this->getEntityManager()->createQuery('
                    SELECT i
                    FROM DeskPRO:Feedback i INDEX BY i.id
                    WHERE i.status = ?0 AND i.category IN (?1)
                    ORDER BY i.id DESC
                ')->setMaxResults($num)->execute([$status, $cat_ids]);
            } else {
                $feedback = $this->getEntityManager()->createQuery('
                    SELECT i
                    FROM DeskPRO:Feedback i INDEX BY i.id
                    WHERE i.status = ?0
                    ORDER BY i.id DESC
                ')->setMaxResults($num)->execute([$status]);
            }
        }

        return $feedback;
    }

    public function getReportAssociations()
    {
        return [
            'views' => [
                'conditions'   => '%1$s.page_type = "deskpro.feedback_view" AND %1$s.page_id = %2$s.id',
                'targetEntity' => 'DeskPRO\\Bundle\\AppBundle\\Entity\\HitRecord',
            ],
            'ratings' => [
                'conditions'   => '%1$s.object_type = \'feedback\' AND %1$s.object_id = %2$s.id',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Rating',
            ],
        ];
    }
}
