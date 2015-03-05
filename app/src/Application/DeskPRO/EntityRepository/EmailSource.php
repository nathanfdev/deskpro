<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;


class EmailSource extends AbstractEntityRepository
{
    /**
     * @param  array $types
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
     * @param  array $types
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
     * @param  array $types
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
     * @param string $email      The email to check
     * @param int    $lock_time  How long a lock is considered for
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

        $active_reject_id = $db->fetchColumn("
            SELECT id
            FROM email_sources
            WHERE date_created >= ? AND from_email = ?
            ORDER BY id DESC
            LIMIT 1
        ", array(date('Y-m-d H:i:s', time()-$lock_time), $email));

        if (!$active_reject_id) {
            return false;
        }

        #------------------------------
        # We need to double-check that the record we just
        # got isn't a reject from a previously set lock which could now be expired
        #------------------------------

        // Find the last real message
        $last_message_id = $db->fetchColumn("
            SELECT id
            FROM email_sources
            WHERE from_email = ? AND !(status = 'rejected' AND error_code = 'rate_limit')
            ORDER BY id DESC
            LIMIT 1
        ", array($email));

        if ($last_message_id) {
            $reject_start = $db->fetchColumn("
                SELECT date_created
                FROM email_sources
                WHERE from_email = ? AND status = 'rejected' AND error_code = 'rate_limit' AND id > ?
                ORDER BY id ASC
                LIMIT 1
            ", array($email, $last_message_id));
        } else {
            $reject_start = $db->fetchColumn("
                SELECT date_created
                FROM email_sources
                WHERE from_email = ? AND status = 'rejected' AND error_code = 'rate_limit' AND id < ?
                ORDER BY id ASC
                LIMIT 1
            ", array($email, $active_reject_id));
        }

        if (!$reject_start) {
            return false;
        }

        $time = \DateTime::createFromFormat('Y-m-d H:i:s', $reject_start)->getTimestamp();

        if (($time+$lock_time) > time()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Counts non-rejected messages within $time. If there was a ratelimit active, we count
     * from the last rate limit.
     *
     * @param string $email
     * @param int $time
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
        ", array(date('Y-m-d H:i:s', time()-$time), $email));

        if ($reject_id) {
            $count = $db->fetchColumn("
                SELECT COUNT(*)
                FROM email_sources
                WHERE date_created >= ? AND from_email = ? AND !(status = 'rejected' AND error_code = 'rate_limit') AND id > ?
            ", array(date('Y-m-d H:i:s', time()-$time), $email, $reject_id));
        } else {
            $count = $db->fetchColumn("
                SELECT COUNT(*)
                FROM email_sources
                WHERE date_created >= ? AND from_email = ? AND !(status = 'rejected' AND error_code = 'rate_limit')
            ", array(date('Y-m-d H:i:s', time()-$time), $email));
        }

        return $count;
    }
}
