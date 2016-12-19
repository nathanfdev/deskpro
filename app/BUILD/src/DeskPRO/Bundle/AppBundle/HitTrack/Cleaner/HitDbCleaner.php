<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\HitTrack\Cleaner;

use Doctrine\DBAL\Connection;

class HitDbCleaner implements HitCleanerInterface
{
    const MAX_RECORDS = 7500;

    /**
     * @var Connection
     */
    private $db;

    /**
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @param \DateTime $last_clean
     */
    public function clean(\DateTime $last_clean)
    {
        $top_id = $this->db->fetchColumn('SELECT id FROM hit_record ORDER BY id DESC LIMIT 1');

        if (!$top_id) {
            return;
        }

        $clean_below_id = $top_id - self::MAX_RECORDS;

        if ($clean_below_id <= 1) {
            return;
        }
        $has_below = $this->db->fetchColumn('SELECT id FROM hit_record WHERE id < ? ORDER BY id DESC LIMIT 1', [$clean_below_id]);

        if (!$has_below) {
            return;
        }

        $this->db->executeUpdate('
            DELETE FROM hit_record
            WHERE id < ?
        ', [$clean_below_id]);
    }
}
