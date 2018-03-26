<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;

class LogItem extends AbstractEntityRepository
{
    /**
     * @param string $job_id
     * @param int    $priority
     * @param int    $from
     * @param int    $limit
     *
     * @return array
     */
    public function getCronLogs($job_id, $priority, $from = 0, $limit = 100)
    {
        return $this->getEntityManager()->getConnection()->fetchAll(
            '
            SELECT log_name, session_name, message, priority, UNIX_TIMESTAMP(date_created) AS date_created
            FROM log_items
            WHERE log_name LIKE ? AND priority <= ?
            ORDER BY id DESC
            LIMIT ?, ?
            ',
            [$job_id, $priority, $from, $limit],
            [\PDO::PARAM_INT, \PDO::PARAM_INT, \PDO::PARAM_INT, \PDO::PARAM_INT]
        );
    }

    /**
     * @param string $job_id
     * @param int    $priority
     * @param int    $per_page
     *
     * @return int
     */
    public function getCronPagesCount($job_id, $priority, $per_page = 100)
    {
        $q = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(l)')
            ->from('DeskPRO:LogItem', 'l')
            ->where('l.log_name LIKE :job_id AND l.priority <= :priority')
            ->setParameter('job_id', $job_id)
            ->setParameter('priority', $priority);

        $count = (int) $q->getQuery()->getSingleScalarResult();

        return ceil($count / $per_page);
    }

    public function findBySn($log_sn)
    {
        try {
            return $this->_em->createQuery('
                SELECT l
                FROM DeskPRO:LogItem l
                WHERE l.session_name = ?1
                ORDER BY l.id DESC
            ')->setParameter(1, $log_sn)
              ->setMaxResults(1)
              ->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return;
        }
    }

    /**
     * Count all error log items.
     *
     * @return int
     */
    public function getErrorLogsCount()
    {
        return App::getDb()->fetchColumn('
            SELECT COUNT(*)
            FROM log_items
            WHERE log_name = ?
        ', ['error_log']);
    }

    /**
     * Get an array of error logs.
     *
     * @param int $page
     * @param int $per_page
     *
     * @return array
     */
    public function getErrorLogs($page = 1, $per_page = 25)
    {
        $offset = max(0, $page - 1) * $per_page;

        return $this->_em->createQuery('
            SELECT l
            FROM DeskPRO:LogItem l
            WHERE l.log_name = ?1
            ORDER BY l.id DESC
        ')->setParameter(1, 'error_log')
          ->setFirstResult($offset)
          ->setMaxResults($per_page)
          ->execute();
    }

    /**
     * Deletes all error logs.
     *
     * @return bool
     */
    public function clearAllErrorLogs()
    {
        App::getDb()->executeUpdate('DELETE FROM log_items WHERE log_name = ?', ['error_log']);

        return true;
    }
}
