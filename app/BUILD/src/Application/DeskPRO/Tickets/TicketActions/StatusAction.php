<?php

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
        if (!App::getContainer()->getTicketStatuses()->isValidStatusCode($status, true)) {
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

        if ($this->isDeletedOrSpamStatus($this->status) && !$person->getPermissionsManager()->TicketChecker->canDelete($ticket)) {
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
        $ticket->setTicketStatus(App::getContainer()->getTicketStatuses()->findStatusOrException($this->status, false, true));

        if ($this->getMetaData('is_preview')) {
            return;
        }

        if ($ticket->isDeleted()) {
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
        return App::getTranslator()->phrase('admin.tickets.set_status_to_x', [
            'status' => App::getContainer()->getTicketStatuses()->findStatusOrException($this->status, false, true)->getTitle(),
        ]);
    }

    /**
     * @param string $statusCode
     *
     * @return bool
     */
    protected function isDeletedOrSpamStatus($statusCode)
    {
        $statuses = App::getContainer()->getTicketStatuses();

        return in_array($statusCode, [
            $statuses->getDeletedStatus()->getStatusCode(),
            $statuses->getSpamStatus()->getStatusCode(),
        ]);
    }
}
