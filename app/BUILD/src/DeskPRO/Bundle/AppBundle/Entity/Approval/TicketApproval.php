<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Approval;

use Application\DeskPRO\Entity\Ticket;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ApprovalType
 *
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\TicketApprovalRepository")
 * @ORM\Table(name="ticket_approvals")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @category Entities
 */
class TicketApproval extends AbstractBaseApproval implements TicketApprovalInterface
{
    /**
     * @var Ticket
     *
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Ticket")
     * @ORM\JoinColumn(name="ticket_id", nullable=false, onDelete="CASCADE")
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     * @JMS\Accessor(getter="getTicketId")
     */
    protected $ticket;

    /**
     * @return Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @param Ticket $ticket
     * @return self
     */
    public function setTicket(Ticket $ticket)
    {
        $this->setModelField('ticket', $ticket);

        return $this;
    }

    /**
     * @return int
     */
    public function getTicketId()
    {
        return $this->ticket->getId();
    }
}
