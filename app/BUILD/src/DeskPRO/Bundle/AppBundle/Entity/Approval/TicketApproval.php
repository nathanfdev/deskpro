<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Approval;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkRoute;
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
 * @PortalLinkRoute("ticket_approvals_view", route_param_map={"id"="id"})
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
     * @JMS\Type("entity<Application\DeskPRO\Entity\Ticket>")
     */
    protected $ticket;

    /**
     * {@inheritDoc}
     */
    protected function getExtraApproversWhenCreatingFromTemplate(
        EntityManagerInterface $em,
        SelectedApprovers $selectedApprovers
    ) {
        if ($selectedApprovers->hasTicketUser()) {
            $this->addApprover($this->getTicket()->getPerson());
        }
    }

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
}
