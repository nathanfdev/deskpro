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

namespace Application\DeskPRO\EntityRepository;

class EmailSource extends AbstractEntityRepository
{
    /**
     * @param array $types
     *
     * @return int
     */
    public function countAllSources(array $types)
    {
        $params = array_values($types);

        $types_place = implode(',', array_fill(0, count($types), '?'));

        $count = $this->_em->getConnection()->fetchColumn("
            SELECT COUNT(*)
            FROM {$this->getTableName()}
            WHERE object_type IN ($types_place)
        ", $params);

        return $count;
    }

    /**
     * @param array $types
     *
     * @return int
     */
    public function countErrorStatus(array $types)
    {
        $params = array_values($types);

        $types_place = implode(',', array_fill(0, count($types), '?'));

        $count = $this->_em->getConnection()->fetchColumn("
            SELECT COUNT(*)
            FROM {$this->getTableName()}
            WHERE object_type IN ($types_place) AND status = 'error'
        ", $params);

        return $count;
    }

    /**
     * @param array $types
     *
     * @return int
     */
    public function countRejectionStatus(array $types)
    {
        $params = array_values($types);

        $types_place = implode(',', array_fill(0, count($types), '?'));

        $count = $this->_em->getConnection()->fetchColumn("
            SELECT COUNT(*)
            FROM {$this->getTableName()}
            WHERE object_type IN ($types_place) AND status = 'rejected'
        ", $params);

        return $count;
    }

    /**
     * Check to see if $email is currently rate limited.
     *
     * @param string $email     The email to check
     * @param int    $lock_time How long a lock is considered for
     *
     * @return bool
     */
    public function isEmailAddressRateLimited($email, $lock_time)
    {
        /** @var \Application\DeskPRO\DBAL\Connection $db */
        $db = $this->_em->getConnection();

        $email = strtolower($email);

        if (!$lock_time) {
            return false;
        }

        /*
         * ----AR---|---R-R-------------RAAR|NOW
         *      |-----------------------|  |----
         *               lock time
         *
         * - find last accepted (A) within lock time
         * - find first rejected (R) after (A)
         *
         */
        $last_accepted_id = $db->fetchColumn(
            '
            SELECT id
            FROM email_sources
            WHERE date_created >= ? AND from_email = ? AND !(status = "rejected" AND error_code = "rate_limit")
            ORDER BY id DESC
            LIMIT 1
        ',
            [date('Y-m-d H:i:s', time() - $lock_time), $email]
        );

        if (!$last_accepted_id) {
            return false;
        }

        return (bool) $db->fetchColumn(
            '
            SELECT id
            FROM email_sources
            WHERE id > ? AND from_email = ? AND status = "rejected" AND error_code = "rate_limit"
            LIMIT 1
        ',
            [$last_accepted_id, $email]
        );
    }

    /**
     * Counts non-rejected messages within $time. If there was a ratelimit active, we count
     * from the last rate limit.
     *
     * @param string $email
     * @param int    $time
     *
     * @return int
     */
    public function countEmailsWithinTime($email, $time)
    {
        /** @var \Application\DeskPRO\DBAL\Connection $db */
        $db = $this->_em->getConnection();

        $email = strtolower($email);

        // Sometimes you might have a threshold time lower than
        // the rejected time. But we should only count since last reject finished.
        $reject_id = $db->fetchColumn("
            SELECT id
            FROM email_sources
            WHERE date_created >= ? AND from_email = ? AND status = 'rejected' AND error_code = 'rate_limit'
            ORDER BY id DESC
            LIMIT 1
        ", [date('Y-m-d H:i:s', time() - $time), $email]);

        if ($reject_id) {
            $count = $db->fetchColumn("
                SELECT COUNT(*)
                FROM email_sources
                WHERE date_created >= ? AND from_email = ? AND !(status = 'rejected' AND error_code = 'rate_limit') AND id > ?
            ", [date('Y-m-d H:i:s', time() - $time), $email, $reject_id]);
        } else {
            $count = $db->fetchColumn("
                SELECT COUNT(*)
                FROM email_sources
                WHERE date_created >= ? AND from_email = ? AND !(status = 'rejected' AND error_code = 'rate_limit')
            ", [date('Y-m-d H:i:s', time() - $time), $email]);
        }

        return $count;
    }
}
