<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
