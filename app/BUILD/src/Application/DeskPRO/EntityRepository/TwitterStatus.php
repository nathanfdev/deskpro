<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class TwitterStatus extends AbstractEntityRepository
{
    public function getByTwitterStatusId($id)
    {
        return $this->getEntityManager()->createQuery('
            SELECT s
            FROM DeskPRO:TwitterStatus s
            WHERE s.id = ?0
        ')->setParameters([$id])->getOneOrNullResult();
    }

    /**
     * @param int        $id
     * @param array|null $from_user_ids   If not null, only from these users
     * @param bool       $includeArchived (optional)
     * @param string     $sortByDate      (optional)
     * @param int        $limit           (optional)
     * @param int        $page            (optional)
     *
     * @return array
     */
    public function findMessagesForUserId($id, array $from_user_ids = null, $includeArchived = false, $sortByDate = 'asc', $limit = 25, $page = 1)
    {
        if ($from_user_ids !== null && !$from_user_ids) {
            return [];
        }

        $query = '
            SELECT s
            FROM DeskPRO:TwitterStatus s INDEX BY s.id
            WHERE s.recipient IS NOT NULL
        ';

        if ($from_user_ids) {
            if (in_array($id, $from_user_ids)) {
                // make sure we can see anything this account sent
                $query .= ' AND ((s.user = :user_id) OR (s.user IN (:from_user_ids) AND s.recipient = :user_id)) ';
            } else {
                $from_user_ids[] = $id;
                $query .= ' AND ((s.user = :user_id AND s.recipient IN (:from_user_ids)) OR (s.user IN (:from_user_ids) AND s.recipient = :user_id)) ';
            }
            $params = ['user_id' => $id, 'from_user_ids' => $from_user_ids];
        } else {
            $query .= ' AND (s.user = :user_id OR s.recipient = :user_id) ';
            $params = ['user_id' => $id];
        }

        if (!$includeArchived) {
            $query .= ' AND s.is_archived = 0 ';
        }

        $query .= sprintf('
            ORDER BY s.date_created %s
        ', $this->normalizeSortByDate($sortByDate));

        return $this
            ->getEntityManager()
            ->createQuery($query)
            ->setMaxResults($limit)
            ->setFirstResult($this->calculateOffset($limit, $page))
            ->execute($params);
    }

    /**
     * @param int        $id
     * @param array|null $from_user_ids   If not null, only from these users
     * @param bool       $includeArchived (optional)
     *
     * @return int
     */
    public function countMessagesForUserId($id, array $from_user_ids = null, $includeArchived = false)
    {
        if ($from_user_ids !== null && !$from_user_ids) {
            return 0;
        }

        $query = '
            SELECT COUNT(s.id)
            FROM DeskPRO:TwitterStatus s
            WHERE s.recipient IS NOT NULL
        ';

        if ($from_user_ids) {
            if (in_array($id, $from_user_ids)) {
                // make sure we can see anything this account sent
                $query .= ' AND ((s.user = :user_id) OR (s.user IN (:from_user_ids) AND s.recipient = :user_id)) ';
            } else {
                $from_user_ids[] = $id;
                $query .= ' AND ((s.user = :user_id AND s.recipient IN (:from_user_ids)) OR (s.user IN (:from_user_ids) AND s.recipient = :user_id)) ';
            }
            $params = ['user_id' => $id, 'from_user_ids' => $from_user_ids];
        } else {
            $query .= ' AND (s.user = :user_id OR s.recipient = :user_id) ';
            $params = ['user_id' => $id];
        }

        if (!$includeArchived) {
            $query .= ' AND s.is_archived = 0 ';
        }

        return $this
            ->getEntityManager()
            ->createQuery($query)
            ->setParameters($params)
            ->getSingleScalarResult();
    }

    /**
     * @param int    $id
     * @param bool   $includeArchived (optional)
     * @param string $sortByDate      (optional)
     * @param int    $limit           (optional)
     * @param int    $page            (optional)
     *
     * @return array
     */
    public function findOutgoingForUserId($id, $includeArchived = false, $sortByDate = 'asc', $limit = 25, $page = 1)
    {
        $query = '
            SELECT s
            FROM DeskPRO:TwitterStatus s INDEX BY s.id
            WHERE s.user = :user_id
                AND s.recipient IS NULL
        ';

        if (!$includeArchived) {
            $query .= ' AND s.is_archived = 0 ';
        }

        $query .= sprintf('
            ORDER BY s.date_created %s
        ', $this->normalizeSortByDate($sortByDate));

        return $this
            ->getEntityManager()
            ->createQuery($query)
            ->setMaxResults($limit)
            ->setFirstResult($this->calculateOffset($limit, $page))
            ->execute([
                'user_id' => $id,
            ]);
    }

    /**
     * @param int    $id
     * @param bool   $includeArchived (optional)
     * @param string $sortByDate      (optional)
     * @param int    $limit           (optional)
     * @param int    $page            (optional)
     *
     * @return array
     */
    public function findRepliesForUserId($id, $includeArchived = false, $sortByDate = 'asc', $limit = 25, $page = 1)
    {
        $query = '
            SELECT r
            FROM DeskPRO:TwitterStatus r INDEX BY s.id
            WHERE r.in_reply_to_status IN (
                SELECT s.id
                FROM DeskPRO:TwitterStatus s
                WHERE s.user = :user_id
            )
        ';

        if (!$includeArchived) {
            $query .= ' AND r.is_archived = 0 ';
        }

        $query .= sprintf('
            ORDER BY s.date_created %s
        ', $this->normalizeSortByDate($sortByDate));

        return $this
            ->getEntityManager()
            ->createQuery($query)
            ->setMaxResults($limit)
            ->setFirstResult($this->calculateOffset($limit, $page))
            ->execute([
                'user_id' => $id,
            ]);
    }

    /**
     * @param int        $id
     * @param array|null $from_user_ids   If not null, only from these users
     * @param bool       $includeArchived (optional)
     * @param string     $sortByDate      (optional)
     * @param int        $limit           (optional)
     * @param int        $page            (optional)
     *
     * @return array
     */
    public function findMentionsForUserId($id, array $from_user_ids = null, $includeArchived = false, $sortByDate = 'asc', $limit = 25, $page = 1)
    {
        if ($from_user_ids !== null && !$from_user_ids) {
            return [];
        }

        $query = '
            SELECT s
            FROM DeskPRO:TwitterStatus s INDEX BY s.id
            INNER JOIN s.mentions m
        ';

        $params = [
            'user_id' => $id,
        ];

        if ($from_user_ids) {
            $query .= 'WHERE ((m.user = :user_id AND s.user IN (:from_user_ids)) OR (s.user = :user_id AND m.user IN (:from_user_ids))) ';
            $params['from_user_ids'] = $from_user_ids;
        } else {
            $query .= 'WHERE m.user = :user_id';
        }

        if (!$includeArchived) {
            $query .= ' AND s.is_archived = 0 ';
        }

        $query .= sprintf('
            ORDER BY s.date_created %s
        ', $this->normalizeSortByDate($sortByDate));

        return $this
            ->getEntityManager()
            ->createQuery($query)
            ->setMaxResults($limit)
            ->setFirstResult($this->calculateOffset($limit, $page))
            ->execute($params);
    }

    /**
     * @param int        $id
     * @param array|null $from_user_ids   If not null, only from these users
     * @param bool       $includeArchived (optional)
     *
     * @return array
     */
    public function countMentionsForUserId($id, array $from_user_ids = null, $includeArchived = false)
    {
        if ($from_user_ids !== null && !$from_user_ids) {
            return 0;
        }

        $query = '
            SELECT COUNT(s.id)
            FROM DeskPRO:TwitterStatus s
            LEFT JOIN s.mentions m
        ';

        $params = [
            'user_id' => $id,
        ];

        if ($from_user_ids) {
            $query .= 'WHERE ((m.user = :user_id AND s.user IN (:from_user_ids)) OR (s.user = :user_id AND m.user IN (:from_user_ids))) ';
            $params['from_user_ids'] = $from_user_ids;
        } else {
            $query .= 'WHERE m.user = :user_id';
        }

        if (!$includeArchived) {
            $query .= ' AND s.is_archived = 0 ';
        }

        return $this
            ->getEntityManager()
            ->createQuery($query)
            ->setParameters($params)
            ->getSingleScalarResult();
    }

    /**
     * @param string $sortByDate (optional)
     *
     * @return string
     */
    protected function normalizeSortByDate($sortByDate = 'asc')
    {
        // check that sort by date is asc or desc
        if (!in_array(strtolower($sortByDate), ['asc', 'desc'])) {
            $sortByDate = 'asc';
        }

        return strtoupper($sortByDate);
    }

    /**
     * @param int $limit
     * @param int $page
     *
     * @return int
     */
    protected function calculateOffset($limit, $page)
    {
        $page = max(1, intval($page));

        return ($page - 1) * $limit;
    }
}
