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
    public function getLatest()
    {
        $query = $this->createQueryBuilder('ss');

        $query->setMaxResults(1);

        $query->orderBy('ss.date_created', 'DESC');

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @param \DateTime      $start
     * @param \DateTime|null $end
     * @param array|null     $in_accounts
     * @param null           $limitHint   Hint at what the max count we care about. We stop if we go over this to save time
     *
     * @return int|mixed
     */
    public function countSendingBetween(\DateTime $start, \DateTime $end = null, array $in_accounts = null, $limitHint = null)
    {
        if (!$end) {
            $end = new \DateTime();
        }

        $countSelects = [
            'COUNT(*) AS count',

            // counts how many actual recipients (e.g. multiple TOs or BCCs)
            'SUM((LENGTH(to_emails)-LENGTH(REPLACE(to_emails, "@", ""))) + COALESCE(LENGTH(bcc_emails)-LENGTH(REPLACE(bcc_emails, "@", "")), 0)) AS count',
        ];

        $result = 0;

        if ($in_accounts) {
            $prevResult = 0;
            foreach ($countSelects as $s) {
                $result = $this->getEntityManager()->getConnection()->fetchColumn("
                    SELECT $s
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

                if ($limitHint && $result >= $limitHint) {
                    return $result;
                }

                $result     = max($result, $prevResult);
                $prevResult = $result;
            }
        } else {
            $in_accounts = array_map('intval', $in_accounts);
            if (!$in_accounts) {
                $in_accounts = [0];
            }
            $in_accounts = implode(',', $in_accounts);

            $prevResult = 0;
            foreach ($countSelects as $s) {
                $result = $this->getEntityManager()->getConnection()->fetchColumn("
                    SELECT $s
                    FROM sendmail_sources
                    WHERE
                      status IN ('complete', 'pending', 'processing', 'retry')
                      AND date_created > ?
                      AND date_status BETWEEN ? AND ?
                      AND account_id IN ($in_accounts)
                ", [
                    $start->format('Y-m-d H:i:s'),
                    $start->format('Y-m-d H:i:s'),
                    $end->format('Y-m-d H:i:s'),
                ]);

                if ($limitHint && $result >= $limitHint) {
                    return $result;
                }

                $result     = max($result, $prevResult);
                $prevResult = $result;
            }
        }

        return $result;
    }
}
