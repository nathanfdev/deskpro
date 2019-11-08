<?php

namespace DeskPRO\Bundle\AppBundle\Ticket\Timeline\Line;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;

/**
 * Class TicketApprovalLine
 *
 * @package DeskPRO\Bundle\AppBundle\Ticket\Timeline\Line
 */
class TicketApprovalLine implements LineInterface
{
    /**
     * @var TicketApproval
     */
    private $approval;

    /**
     * @var Person
     */
    private $person;

    /**
     * TicketApprovalLine constructor.
     *
     * @param TicketApproval $approval
     * @param Person $person
     */
    public function __construct(TicketApproval $approval, Person $person)
    {
        $this->approval = $approval;
        $this->person = $person;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return 'ticket_approval';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTime()
    {
        switch ($this->approval->getStatus()) {
            case TicketApproval::STATUS_PENDING:
                return $this->approval->getCreatedAt();
            case TicketApproval::STATUS_CANCELLED:
                return $this->approval->getCancelledAt();
            case TicketApproval::STATUS_APPROVED:
            case TicketApproval::STATUS_REJECTED:
                return $this->approval->getCompletedAt();
        }

        throw new \RuntimeException('Unable to determine date/time for ticket approval log entry');
    }

    /**
     * {@inheritDoc}
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @return TicketApproval
     */
    public function getApproval()
    {
        return $this->approval;
    }
}
