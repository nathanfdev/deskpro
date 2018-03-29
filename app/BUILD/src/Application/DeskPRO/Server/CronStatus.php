<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Server;

use Application\DeskPRO\DBAL\Connection;

class CronStatus
{
    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * @var \DateTime
     */
    private $last_run_ts;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @return int
     */
    public function getLastRunTimestamp()
    {
        if ($this->last_run_ts !== null) {
            return $this->last_run_ts;
        }

        $this->last_run_ts = $this->db->fetchColumn('
            SELECT value
            FROM settings
            WHERE name = ?
        ', ['core.last_cron_run']);

        if (!$this->last_run_ts) {
            $this->last_run_ts = 0;
        }

        return $this->last_run_ts;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastRunDate()
    {
        $ts = $this->getLastRunTimestamp();
        if (!$ts) {
            return;
        }

        $date = new \DateTime("@$ts");

        return $date;
    }

    /**
     * Get how long it's been since the last cron.
     *
     * @return int
     */
    public function getSecsSinceLastRun()
    {
        $now  = time();
        $last = $this->getLastRunTimestamp();

        return $now - $last;
    }

    /**
     * Based on the last time cron was run, guess if there's a problem.
     *
     * @return bool
     */
    public function guessIsProblem()
    {
        return $this->getSecsSinceLastRun() > 300;
    }

    /**
     * @return array
     */
    public function getCronBootErrors()
    {
        return;
    }
}
