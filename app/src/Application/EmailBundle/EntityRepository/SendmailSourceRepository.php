<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
            array($refs),
            array(Connection::PARAM_STR_ARRAY)
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
     *
     * @return int
     */
    public function countSendingBetween(\DateTime $start, \DateTime $end = null, array $in_accounts = null)
    {
        if (!$end) {
            $end = new \DateTime();
        }

        if ($in_accounts) {
            return $this->getEntityManager()->getConnection()->fetchColumn("
                SELECT COUNT(*)
                FROM sendmail_sources
                WHERE
                  status IN ('complete', 'pending', 'processing', 'retry')
                  AND date_created > ?
                  AND date_status BETWEEN ? AND ?
            ", array(
                $start->format('Y-m-d H:i:s'),
                $start->format('Y-m-d H:i:s'),
                $end->format('Y-m-d H:i:s'),
            ));
        } else {
            $in_accounts = array_map('intval', $in_accounts);
            if (!$in_accounts) {
                $in_accounts = array(0);
            }
            $in_accounts = implode(',', $in_accounts);

            return $this->getEntityManager()->getConnection()->fetchColumn("
                SELECT COUNT(*)
                FROM sendmail_sources
                WHERE
                  status IN ('complete', 'pending', 'processing', 'retry')
                  AND date_created > ?
                  AND date_status BETWEEN ? AND ?
                  AND account_id IN ($in_accounts)
            ", array(
                $start->format('Y-m-d H:i:s'),
                $start->format('Y-m-d H:i:s'),
                $end->format('Y-m-d H:i:s'),
            ));
        }
    }
}
