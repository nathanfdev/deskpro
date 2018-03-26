<?php

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
