<?php

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
            ", [$date_cut->format('Y-m-d H:i:s')]);
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
            $db->update('email_accounts', ['is_read_active' => 1], ['id' => $account_id]);
        }

        $db->commit();

        if ($account_id) {
            return new Task(['account_id' => $account_id]);
        }

        return;
    }
}
