<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\EmailBundle\EntityRepository;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\EntityRepository\AbstractEntityRepository;

class SendmailSourceRepository extends AbstractEntityRepository
{
    public function getIdsByRefs(array $refs)
    {
        return $this->getEntityManager()->getConnection()->executeQuery(
            sprintf('select id, ref from %s where ref in (?)', $this->getTableName()),
            [$refs],
            [Connection::PARAM_STR_ARRAY]
        )->fetchAll();
    }

    /**
     * returns the "newest" SendmailSource.
     */
    public function getLatest($to = null)
    {
        $query = $this->createQueryBuilder('ss');

        if ($to) {
            $query->andWhere('ss.to_emails LIKE :to');
            $query->setParameter('to', $to);
        }

        $query->setMaxResults(1);

        $query->orderBy('ss.date_created', 'DESC');

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @param \DateTime      $start      Start of the date range to check in
     * @param \DateTime|null $end        End of the date range to check in
     * @param array|null     $inAccounts Specific accounts to look at
     *
     * @return int|mixed
     */
    public function countSendingBetween(\DateTime $start, \DateTime $end = null, array $inAccounts = null)
    {
        if (!$end) {
            $end = new \DateTime();
        }

        if (!$inAccounts) {
            $result = $this->getEntityManager()->getConnection()->fetchColumn("
                    SELECT SUM(num_targets)
                    FROM sendmail_sources
                    WHERE
                      status IN ('complete', 'pending', 'processing', 'retry')
                      AND date_created > ?
                      AND date_status BETWEEN ? AND ?
                ", [
                $start->format('Y-m-d H:i:s'),
                $start->format('Y-m-d H:i:s'),
                $end->format('Y-m-d H:i:s'),
            ]);
        } else {
            $inAccounts = array_map('intval', $inAccounts);
            if (!$inAccounts) {
                $inAccounts = [0];
            }

            $result = $this->getEntityManager()->getConnection()->fetchColumn("
                    SELECT SUM(num_targets)
                    FROM sendmail_sources
                    WHERE
                      status IN ('complete', 'pending', 'processing', 'retry')
                      AND date_created > ?
                      AND date_status BETWEEN ? AND ?
                      AND email_account_id IN (?)
                ", [
                $start->format('Y-m-d H:i:s'),
                $start->format('Y-m-d H:i:s'),
                $end->format('Y-m-d H:i:s'),
                $inAccounts,
            ], 0, [\PDO::PARAM_STR, \PDO::PARAM_STR, \PDO::PARAM_STR, Connection::PARAM_INT_ARRAY]);
        }

        return $result;
    }
}
