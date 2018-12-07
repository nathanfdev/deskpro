<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\People;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Tickets\Util as TicketUtil;
use Doctrine\ORM\EntityManager;

class Purger implements PersonContextInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    protected $db;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * Who is performing the delete.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person_context;

    /**
     * @var DeskproBlobStorage
     */
    protected $blobStorage;

    public function __construct(Person $person, EntityManager $em, DeskproBlobStorage $blobStorage = null)
    {
        $this->person      = $person;
        $this->em          = $em;
        $this->db          = $em->getConnection();
        $this->blobStorage = $blobStorage;
    }

    public function purge()
    {
        $this->db->beginTransaction();
        try {
            $this->purgeCallRecords();
            $this->purgeTickets();

            $this->db->delete('people', ['id' => $this->person->getId()]);
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function purgeCallRecords()
    {
        if ($this->blobStorage) {
            TicketUtil::deletePersonCallRecords($this->person, $this->em, $this->blobStorage);
        }
    }

    /**
     * Purge all the tickets belonging to a user.
     */
    public function purgeTickets()
    {
        //------------------------------
        // Clean up their messages
        //------------------------------

        // This fixes ticket messages becoming written by a null author
        // when the original account is deleted but the ticket remains
        // (e.g., the ticket would stay if it was reset to a new user)

        $orig_author_line = 'Originally written by: '.htmlspecialchars($this->person->getDisplayContact())."<br/><br/><br/>\n\n\n";

        $this->db->executeUpdate('
            UPDATE tickets_messages
                JOIN tickets ON (tickets.id = tickets_messages.ticket_id)
            SET tickets_messages.person_id = tickets.person_id, tickets_messages.message = CONCAT(?, tickets_messages.message)
            WHERE tickets.person_id != ? AND tickets_messages.person_id = ?
        ', [$orig_author_line, $this->person->id, $this->person->id]);

        //------------------------------
        // Fetch ticket IDs
        //------------------------------

        $ticket_ids = $this->db->fetchAllCol('
            SELECT id FROM tickets WHERE person_id = ?
        ', [$this->person->getId()]);

        //------------------------------
        // Attachments and links
        //------------------------------

        foreach ($ticket_ids as $ticket_id) {
            TicketUtil::deleteTicketAttachments($ticket_id, $this->db);
        }

        //------------------------------
        // Insert delete logs
        //------------------------------

        $by_person_id = null;
        if ($this->person_context) {
            $by_person_id = $this->person_context->getId();
        }

        $date_str   = date('Y-m-d H:i:s');
        $reason_str = 'User was deleted';

        $inserts = [];

        foreach ($ticket_ids as $ticket_id) {
            $inserts[] = ['ticket_id' => $ticket_id, 'by_person_id' => $by_person_id, 'new_ticket_id' => 0, 'date_created' => $date_str, 'reason' => $reason_str];
        }

        if ($inserts) {
            $this->db->batchInsert('tickets_deleted', $inserts, true);
        }

        //------------------------------
        // Clear out the search tables
        //------------------------------

        $this->db->delete('tickets_search_active', ['person_id' => $this->person->getId()]);
    }

    /**
     * Set the context (who is making these edits).
     *
     * @param Person $person
     */
    public function setPersonContext(Person $person)
    {
        $this->person_context = $person;
    }

    /**
     * @return \Application\DeskPRO\Entity\Person
     */
    public function getPersonContext()
    {
        return $this->person_context;
    }
}
