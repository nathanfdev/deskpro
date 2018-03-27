<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets;

use Doctrine\DBAL\Connection;

class TicketPurger
{
    /** @var \Doctrine\DBAL\Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function purgeSpamAction()
    {
        $this->db->executeUpdate("
            UPDATE blobs
            LEFT JOIN tickets_attachments ON (tickets_attachments.blob_id = blobs.id)
            LEFT JOIN tickets ON (tickets.id = tickets_attachments.ticket_id)
            SET blobs.is_temp = 1
            WHERE
              tickets.status = 'hidden' AND tickets.hidden_status = 'spam'
              AND tickets_attachments.ticket_id IS NOT NULL
        ");

        $this->db->executeUpdate("
            UPDATE blobs
            LEFT JOIN ticket_proc_log ON (ticket_proc_log.blob_id = blobs.id)
            LEFT JOIN tickets ON (tickets.id = ticket_proc_log.ticket_id)
            SET blobs.is_temp = 1
            WHERE
              tickets.status = 'hidden' AND tickets.hidden_status = 'spam'
              AND ticket_proc_log.ticket_id IS NOT NULL
        ");

        $count = $this->db->delete(
            'tickets',
            ['status' => 'hidden', 'hidden_status' => 'spam']
        );

        return $count;
    }

    public function purgeDeletedAction()
    {
        $this->db->executeUpdate("
            UPDATE blobs
            LEFT JOIN tickets_attachments ON (tickets_attachments.blob_id = blobs.id)
            LEFT JOIN tickets ON (tickets.id = tickets_attachments.ticket_id)
            SET blobs.is_temp = 1
            WHERE
              tickets.status = 'hidden' AND tickets.hidden_status = 'deleted'
              AND tickets_attachments.ticket_id IS NOT NULL
        ");

        $this->db->executeUpdate("
            UPDATE blobs
            LEFT JOIN ticket_proc_log ON (ticket_proc_log.blob_id = blobs.id)
            LEFT JOIN tickets ON (tickets.id = ticket_proc_log.ticket_id)
            SET blobs.is_temp = 1
            WHERE
              tickets.status = 'hidden' AND tickets.hidden_status = 'deleted'
              AND ticket_proc_log.ticket_id IS NOT NULL
        ");

        $count = $this->db->delete(
            'tickets',
            ['status' => 'hidden', 'hidden_status' => 'deleted']
        );

        return $count;
    }

    public function purgeAll()
    {
        $this->db->executeUpdate('
            UPDATE blobs
            LEFT JOIN tickets_attachments ON (tickets_attachments.blob_id = blobs.id)
            SET blobs.is_temp = 1
            WHERE tickets_attachments.ticket_id IS NOT NULL
        ');

        $this->db->executeUpdate('
            UPDATE blobs
            LEFT JOIN ticket_proc_log ON (ticket_proc_log.blob_id = blobs.id)
            SET blobs.is_temp = 1
            WHERE ticket_proc_log.ticket_id IS NOT NULL
        ');

        $this->db->executeUpdate('delete from tickets');
        $this->db->executeUpdate('delete from tickets_deleted');
        $this->db->executeUpdate('delete from tickets_flagged');
        $this->db->executeUpdate('delete from tickets_sms');
    }
}
