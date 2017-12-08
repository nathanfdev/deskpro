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
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\TicketChangeTracker;

/**
 * Sets status.
 */
class StatusAction extends AbstractAction implements PermissionableAction
{
    /** @var string */
    protected $status;

    /**
     * @var \Application\DeskPRO\Tickets\TicketChangeTracker
     */
    protected $tracker;

    public function __construct($status, TicketChangeTracker $tracker = null)
    {
        $this->setStatus($status);
        $this->tracker = $tracker;
    }

    public function setStatus($status)
    {
        if (!in_array($status, [
            'awaiting_agent', 'awaiting_user', 'resolved', 'archived',
            'hidden.spam', 'hidden.deleted',
        ])) {
            throw new \InvalidArgumentException("Invalid status `$status`");
        }
        $this->status = $status;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPermission(Ticket $ticket, Person $person)
    {
        // No change, sure they can apply no change
        if ($ticket->getStatusCode() == $this->status) {
            return true;
        }

        if (($this->status == 'hidden.deleted' || $this->status == 'hidden.spam') && !$person->getPermissionsManager()->TicketChecker->canDelete($ticket)) {
            return false;
        }
        if ($this->status == 'awaiting_agent' && !$person->getPermissionsManager()->TicketChecker->canModify($ticket, 'set_awaiting_agent')) {
            return false;
        }
        if ($this->status == 'awaiting_user' && !$person->getPermissionsManager()->TicketChecker->canModify($ticket, 'set_awaiting_user')) {
            return false;
        }
        if ($this->status == 'resolved' && !$person->getPermissionsManager()->TicketChecker->canModify($ticket, 'set_resolved')) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        if (strpos($this->status, '.') !== false) {
            list($status, $hidden_status) = explode('.', $this->status, 2);
        } else {
            $status        = $this->status;
            $hidden_status = null;
        }

        if ($hidden_status) {
            $ticket->setHiddenStatus($hidden_status);
        } else {
            $ticket->setStatus($status);
        }

        if ($this->getMetaData('is_preview')) {
            return;
        }

        if ($ticket->hidden_status == 'deleted') {
            $delete_person = null;
            if ($this->tracker && $this->tracker->getPersonPerformer()) {
                $delete_person = $this->tracker->getPersonPerformer();
            } elseif (defined('DP_INTERFACE') && DP_INTERFACE == 'agent' && App::getCurrentPerson()) {
                $delete_person = App::getCurrentPerson();
            }

            if ($delete_person) {
                App::getDb()->executeUpdate("
                    INSERT INTO tickets_deleted
                        (ticket_id, by_person_id, new_ticket_id, date_created, reason, old_ptac)
                    VALUES
                        (?, ?, 0, ?, '', ?)
                    ON DUPLICATE KEY UPDATE
                        by_person_id = VALUES(by_person_id),
                        new_ticket_id = VALUES(new_ticket_id),
                        reason = VALUES(reason),
                        old_ptac = VALUES(old_ptac)
                ", [$ticket->getId(), $delete_person->getId(), gmdate('Y-m-d H:i:s'), $ticket->auth]);
            }

            App::getOrm()->persist($ticket);
        } else {
            App::getOrm()->persist($ticket);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        if ($ticket->getStatusCode() == $this->status) {
            return [];
        }

        return [
            ['action' => 'status', 'status' => $this->status],
        ];
    }

    /**
     * Get the full status (status.hidden_status).
     *
     * @return string
     */
    public function getFullStatus()
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ActionInterface $otherAction)
    {
        return $otherAction;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($as_html = true)
    {
        $tr = App::getTranslator();

        return $tr->phrase('admin.tickets.set_status_to_x', ['status' => $tr->phrase('agent.tickets.status_'.str_replace('.', '_', $this->status))]);
    }
}
