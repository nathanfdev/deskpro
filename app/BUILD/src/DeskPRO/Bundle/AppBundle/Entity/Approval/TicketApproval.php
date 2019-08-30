<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Approval;

use Application\DeskPRO\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;
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
     * Filter name used in agent UI search templates
     */
    const FILTER_NAME = 'ticket_approval';

    /**
     * @var Ticket
     *
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Ticket", inversedBy="approvals")
     * @ORM\JoinColumn(name="ticket_id", nullable=false, onDelete="CASCADE")
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     * @JMS\Accessor(getter="getTicketId")
     */
    protected $ticket;

    /**
     * {@inheritDoc}
     */
    public function notifyAssociationChanges(EntityManagerInterface $em)
    {
        $this->ticket->getStateChangeRecorder()->record('approvals', null, $this);
    }

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
