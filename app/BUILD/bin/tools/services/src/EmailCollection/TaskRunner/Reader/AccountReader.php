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

namespace DeskPRO\Services\EmailCollection\TaskRunner\Reader;

use DeskPRO\Component\TaskRunner\Reader\ReaderInterface;
use DeskPRO\Component\TaskRunner\Task\Task;

class AccountReader implements ReaderInterface
{
    /**
     * @var callable
     */
    private $db_factory;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * @var int
     */
    private $interval;

    /**
     * @param int      $interval   The min time between each check
     * @param callable $db_factory
     */
    public function __construct($interval, $db_factory)
    {
        $this->interval   = $interval;
        $this->db_factory = $db_factory;
    }

    /**
     * @return \Application\DeskPRO\DBAL\Connection
     */
    private function getDb()
    {
        $this->db = call_user_func($this->db_factory, $this->db);

        return $this->db;
    }

    /**
     * Gets the next account we should process.
     */
    public function getNext()
    {
        $db = $this->getDb();
        $db->beginTransaction();

        if ($this->interval) {
            $date_cut   = new \DateTime("-{$this->interval} seconds");
            $account_id = $db->fetchColumn("
                SELECT id
                FROM email_accounts
                WHERE
                  account_type = 'tickets'
                  AND is_read_active = 0 AND (date_last_incoming IS NULL OR date_last_incoming <= ?)
                  AND is_enabled = 1
                ORDER BY date_read_start ASC
                LIMIT 1
            ", array($date_cut->format('Y-m-d H:i:s')));
        } else {
            $account_id = $db->fetchColumn("
                SELECT id
                FROM email_accounts
                WHERE
                  account_type = 'tickets'
                  AND is_read_active = 0
                  AND is_enabled = 1
                ORDER BY date_read_start ASC
                LIMIT 1
            ");
        }

        // The is_read_active is toggled on/off during dp:collect-email anyway,
        // but by reserving it here, it makes sure there's no races
        if ($account_id) {
            $db->update('email_accounts', array('is_read_active' => 1), array('id' => $account_id));
        }

        $db->commit();

        if ($account_id) {
            return new Task(array('account_id' => $account_id));
        }

        return;
    }
}
