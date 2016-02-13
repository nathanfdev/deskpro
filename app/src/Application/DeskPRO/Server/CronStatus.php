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
        ', array('core.last_cron_run'));

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
