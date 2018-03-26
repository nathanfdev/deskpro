<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketMerge;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketDeleted;
use Application\DeskPRO\ORM\StateChange\Ticket\ChangeMerge;
use Application\DeskPRO\People\PermissionChecker\TicketChecker;
use Application\DeskPRO\People\PersonContextInterface;
use Doctrine\DBAL\Connection;

/**
 * Handles merging of one ticket into the other.
 */
class TicketMerge implements PersonContextInterface
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    private $person;

    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    private $ticket;

    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    private $other_ticket;

    /**
     * The ticket ID (copied because after its deleted, the id would be lost).
     *
     * @var int
     */
    private $other_ticket_id;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * @var \Application\DeskPRO\Tickets\TicketManager
     */
    private $ticket_manager;

    /**
     * @var array
     */
    private $data_lost = [];

    /**
     * @param Person $person_performer
     * @param Ticket $ticket
     * @param Ticket $other_ticket
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Person $person_performer, Ticket $ticket, Ticket $other_ticket)
    {
        $this->em             = App::$container->getEm();
        $this->db             = $this->em->getConnection();
        $this->ticket_manager = App::$container->getTicketManager();

        $this->ticket       = $ticket;
        $this->other_ticket = $other_ticket;
        $this->setPersonContext($person_performer);

        $this->other_ticket_id = $other_ticket->id;

        if ($ticket === $other_ticket) {
            throw new \InvalidArgumentException('You cannot merge a ticket with itself');
        }
    }

    /**
     * @param Person $person
     */
    public function setPersonContext(Person $person)
    {
        $this->person = $person;
    }

    /**
     * @return mixed
     */
    public function checkPersonPermission()
    {
        /** @var TicketChecker $ticketChecker */
        $ticketChecker = $this->person->getPermissionsManager()->TicketChecker;

        return $ticketChecker->canMerge($this->ticket, $this->other_ticket);
    }

    /**
     * Merge the tickets.
     */
    public function merge()
    {
        $this->ticket_manager->markAsManaged($this->ticket);
        $this->ticket_manager->markAsManaged($this->other_ticket);

        try {
            $this->doMerge();
            $this->ticket_manager->markAsUnmanaged($this->ticket);
            $this->ticket_manager->markAsUnmanaged($this->other_ticket);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @throws \Exception
     * @throws \DomainException
     */
    private function doMerge()
    {
        if (!$this->checkPersonPermission()) {
            throw new \DomainException('User does not have permission to merge these tickets');
        }

        $merge_change = new ChangeMerge('merged_from', $this->other_ticket_id, $this->data_lost);
        $this->ticket->getStateChangeRecorder()->recordChange($merge_change);

        $old_id = $this->other_ticket->getId();

        $ticket_person       = $this->ticket->person;
        $other_ticket_person = $this->other_ticket->person;

        // Old ticket set to deleted so proper CM's are sent
        $this->other_ticket->setStatus('hidden.deleted');
        $this->em->persist($this->other_ticket);
        $this->em->flush();

        $this->mergeMessages();
        $this->mergeParticipants();
        $this->mergeLogs();
        $this->mergeMisc();

        /*
         * merge dates:
         *
         * Take the EARLIEST date:
         *  date_feedback_rating
         *  date_created
         *  date_first_agent_assign
         *  date_first_agent_reply
         *  date_resolved
         *  date_archived
         *
         * Take the LATEST date:
         *  date_status
         *  date_agent_waiting
         *  date_user_waiting
         *
         * total_to_first_reply should be max(ticket1, ticket2)
         * total_user_waiting should be max(ticket1, ticket2)
         */

        $n  = $this->ticket;
        $o  = $this->other_ticket;
        $md = function ($prop, $func) use ($n, $o) {
            if (!$n->$prop) {
                return $n->$prop = $o->$prop ?: null;
            }
            if (!$o->$prop) {
                return;
            }

            return $n->$prop = $func($n->$prop, $o->$prop);
        };
        $md('date_feedback_rating', 'min');
        $md('date_created', 'min');
        $md('date_first_agent_reply', 'min');
        $md('date_first_agent_assign', 'min');
        $md('date_last_agent_reply', 'max');
        $md('date_last_user_reply', 'max');
        $md('date_resolved', 'min');
        $md('date_archived', 'min');

        $md('date_status', 'max');
        $md('date_agent_waiting', 'max');
        $md('date_user_waiting', 'max');

        $md('total_to_first_reply', 'max');
        $md('total_user_waiting', 'max');

        // merge waiting times
        $map = [];
        foreach ($n->waiting_times as $time) {
            $map[implode('|', [$time['type'], $time['start'], $time['end']])] = $time;
        }
        foreach ($o->waiting_times as $time) {
            $map[implode('|', [$time['type'], $time['start'], $time['end']])] = $time;
        }
        $n->waiting_times = array_values($map);

        // non-merged fields that we want to log
        $lost_log = [
            'subject' => null,
        ];
        foreach ($lost_log as $prop_name => $title_field) {
            if ($title_field) {
                $this->data_lost[$prop_name] = $this->other_ticket[$prop_name]->$title_field;
            } else {
                $this->data_lost[$prop_name] = $this->other_ticket[$prop_name];
            }
        }

        $standard_prop_names = [
            'agent'         => 'name',
            'agent_team'    => 'name',
            'department'    => 'full_title',
            'language'      => 'title',
            'category'      => 'title',
            'product'       => 'title',
            'workflow'      => 'title',
            'priority'      => 'title',
            'parent_ticket' => 'id',
        ];
        foreach ($standard_prop_names as $prop_name => $title_field) {
            if ($this->ticket[$prop_name] && $this->other_ticket[$prop_name]) {
                $this->data_lost[$prop_name] = $this->other_ticket[$prop_name]->$title_field;
            }

            $prop_standard = new Property\StandardProperty($this->ticket, $this->other_ticket);
            $prop_standard->setProperty($prop_name);
            $prop_standard->setStrategy(Property\StandardProperty::STRATEGY_COMBINE);
            $prop_standard->merge();
        }

        if ($this->ticket->parent_ticket) {
            if ($this->ticket->parent_ticket == $this->ticket || $this->ticket->parent_ticket == $this->other_ticket) {
                $this->ticket->parent_ticket = null;
            }
        }

        $this->ticket->organization = $this->ticket->person->organization;

        ksort($this->data_lost);

        $ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
        foreach ($ticket_field_defs as $f) {
            $prop_field = new Property\CustomField($this->ticket, $this->other_ticket);
            $prop_field->setField($f);
            $prop_field->setStrategy(Property\StandardProperty::STRATEGY_COMBINE);
            $prop_field->merge();

            if ($prop_field->lost) {
                $this->data_lost['fields'][$f->id] = [$f->title, $prop_field->lost];
            }
        }

        $merge_change->setLostData($this->data_lost);

        // If they're different users, then add the old person as a participant on the ticket
        if ($ticket_person->getId() != $other_ticket_person->getId()) {
            $part = $this->ticket->addParticipantPerson($other_ticket_person);
            if ($part && !$part->getId()) {
                $this->em->persist($part);
            }
        }

        $ticket_del = $this->em->find('DeskPRO:TicketDeleted', $this->other_ticket['id']);
        if (!$ticket_del) {
            $ticket_del            = new TicketDeleted();
            $ticket_del->ticket_id = $this->other_ticket['id'];
            $ticket_del->old_ptac  = $this->other_ticket->auth;
            $ticket_del->old_ref   = $this->other_ticket->ref;
        }

        $ticket_del->new_ticket_id = $this->ticket['id'];
        $ticket_del->by_person     = $this->person;
        $ticket_del->reason        = 'Merge into '.$this->ticket['id'];
        $this->em->persist($ticket_del);

        $context = $this->ticket_manager->createAgentExecutorContext($this->person, 'update', 'web');
        $this->ticket_manager->saveTicket($this->ticket, $context);

        $context = $this->ticket_manager->createAgentExecutorContext($this->person, 'noop', 'web');
        $this->ticket_manager->saveTicket($this->other_ticket, $context);

        $this->em->detach($this->other_ticket);
        $this->other_ticket->id = null;

        $this->db->delete('tickets', ['id' => $old_id]);
        $this->db->delete('tickets_search_active', ['id' => $old_id]);
    }

    /**
     * Merges messages.
     */
    private function mergeMessages()
    {
        foreach ($this->other_ticket->messages as $message) {
            $this->ticket->addMessage($message);
            foreach ($message->attachments as $attachment) {
                $this->ticket->addAttachment($attachment);
            }
        }
    }

    /**
     * Merges ticket logs.
     */
    private function mergeLogs()
    {
        $this->db->executeUpdate('
            UPDATE tickets_logs
            SET ticket_id = ?
            WHERE ticket_id = ?
        ', [$this->ticket['id'], $this->other_ticket['id']]);

        $this->db->executeUpdate('
            UPDATE ticket_proc_log
            SET ticket_id = ?
            WHERE ticket_id = ?
        ', [$this->ticket['id'], $this->other_ticket['id']]);
    }

    /**
     * Merges parts.
     */
    private function mergeParticipants()
    {
        foreach ($this->other_ticket->participants as $part) {
            // don't store participants if they are already belongs to a ticket as person or agent of the ticket
            if ($this->ticket->getPerson() === $part->person || $this->ticket->getAgent() === $part->person) {
                continue;
            }

            $this->ticket->addParticipantPerson($part->person);
        }
    }

    /**
     * Merges the rest.
     */
    private function mergeMisc()
    {
        // Flags
        $this->db->executeUpdate('
            UPDATE IGNORE tickets_flagged
            SET ticket_id = ?
            WHERE ticket_id = ?
        ', [$this->ticket['id'], $this->other_ticket['id']]);
        $this->db->delete('tickets_flagged', ['ticket_id' => $this->other_ticket['id']]);

        // Pending articles
        $this->db->executeUpdate('
            UPDATE IGNORE article_pending_create
            SET ticket_id = ?
            WHERE ticket_id = ?
        ', [$this->ticket['id'], $this->other_ticket['id']]);
        $this->db->delete('article_pending_create', ['ticket_id' => $this->other_ticket['id']]);

        // Labels
        $this->db->executeUpdate('
            UPDATE IGNORE labels_tickets
            SET ticket_id = ?
            WHERE ticket_id = ?
        ', [$this->ticket['id'], $this->other_ticket['id']]);
        $this->db->delete('labels_tickets', ['ticket_id' => $this->other_ticket['id']]);

        // Tasks
        $this->db->executeUpdate('
            UPDATE IGNORE task_associations
            SET ticket_id = ?
            WHERE ticket_id = ?
        ', [$this->ticket['id'], $this->other_ticket['id']]);

        // Billing
        $this->db->executeUpdate('
            UPDATE IGNORE ticket_charges
            SET ticket_id = ?
            WHERE ticket_id = ?
        ', [$this->ticket['id'], $this->other_ticket['id']]);

        // Feedback
        $this->db->executeUpdate('
            UPDATE IGNORE ticket_feedback
            SET ticket_id = ?
            WHERE ticket_id = ?
        ', [$this->ticket['id'], $this->other_ticket['id']]);

        // Delete SLAs from old ticket that already exist on new one
        $sla_ids = $this->db->fetchAllCol('SELECT sla_id FROM ticket_slas WHERE ticket_id = ?', [$this->ticket['id']]);
        if ($sla_ids) {
            $this->db->executeQuery('
                DELETE FROM ticket_slas
                WHERE ticket_id = ? AND sla_id IN (?)
            ', [$this->other_ticket['id'], $sla_ids], [\PDO::PARAM_INT, Connection::PARAM_INT_ARRAY]);
        }

        // ... and then move the rest of the SLAs over
        $this->db->executeUpdate('
            UPDATE IGNORE ticket_slas
            SET ticket_id = ?
            WHERE ticket_id = ?
        ', [$this->ticket['id'], $this->other_ticket['id']]);

        // Parent links
        $this->db->executeUpdate('
            UPDATE IGNORE tickets
            SET parent_ticket_id = ?
            WHERE parent_ticket_id = ?
        ', [$this->ticket['id'], $this->other_ticket['id']]);

        // JIRA issues
        $this->db->executeUpdate('
            UPDATE IGNORE jira_issues
            SET ticket_id = ?
            WHERE ticket_id = ?
        ', [$this->ticket['id'], $this->other_ticket['id']]);
    }
}
