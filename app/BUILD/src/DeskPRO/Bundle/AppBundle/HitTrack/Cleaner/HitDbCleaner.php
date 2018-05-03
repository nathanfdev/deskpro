<?php

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
