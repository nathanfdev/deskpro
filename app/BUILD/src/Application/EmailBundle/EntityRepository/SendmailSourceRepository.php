<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
