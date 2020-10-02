<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\JobQueue\Processor\PurgeTicketsProcessor;
use Application\DeskPRO\Tickets\Util as TicketUtil;
use DeskPRO\Bundle\VoiceBundle\JobQueue\Processor\LoadTwilioPriceProcessor;

class TicketPurger
{
    /** @var \Doctrine\DBAL\Connection */
    private $db;

    /**
     * @var int
     */
    private $spamStatusId;

    /**
     * @var int
     */
    private $deletedStatusId;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function purgeSpamAction()
    {
        return $this->purgeByHiddenStatus('spam', $this->getSpamStatusId());
    }

    public function purgeDeletedAction()
    {
        return $this->purgeByHiddenStatus('deleted', $this->getDeletedStatusId());
    }

    public function purgeAll()
    {
        do {
            $ticketIds = $this->db->fetchAllCol(
                'SELECT id FROM tickets WHERE id > ? LIMIT 1000',
                [isset($ticketIds) ? max($ticketIds) : 0]
            );

            foreach ($ticketIds as $ticketId) {
                TicketUtil::deleteTicketAttachments($ticketId, $this->db);
            }
        } while(count($ticketIds) > 0);

        $this->db->executeUpdate('delete from tickets');
        $this->db->executeUpdate('delete from tickets_deleted');
        $this->db->executeUpdate('delete from tickets_flagged');
        $this->db->executeUpdate('delete from tickets_sms');
    }

    /**
     * @param string $type
     * @param int    $statusId
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function purgeByHiddenStatus($type, $statusId)
    {
        // run an async process with a cron job
        App::$container->get('job.queue')->addJob(new Job(PurgeTicketsProcessor::JOB_TYPE, [
            'type' => $type,
        ]));

        return $this->db->fetchColumn(<<<'SQL'
            SELECT COUNT(*)
            FROM tickets
            WHERE status = "hidden" AND ticket_status_id = ?
SQL
            , [$statusId]);
    }

    /**
     * @return int
     */
    protected function getSpamStatusId()
    {
        if (!$this->spamStatusId) {
            $this->spamStatusId = (int) $this->db->fetchColumn("SELECT id FROM ticket_statuses where sys_id = 'spam'");
        }

        return $this->spamStatusId;
    }

    /**
     * @return int
     */
    protected function getDeletedStatusId()
    {
        if (!$this->deletedStatusId) {
            $this->deletedStatusId = (int) $this->db->fetchColumn("SELECT id FROM ticket_statuses where sys_id = 'deleted'");
        }

        return $this->deletedStatusId;
    }
}
