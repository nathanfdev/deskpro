<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Approval;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkRoute;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ApprovalType.
 *
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\TicketApprovalRepository")
 * @ORM\Table(name="ticket_approvals")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 *
 * @PortalLinkRoute("ticket_approvals_view", route_param_map={"id"="id"})
 * @JMS\ExclusionPolicy("all")
 *
 * @AppAssert\Approval\TicketApprovalOrgManagers()
 *
 * @category Entities
 */
class TicketApproval extends AbstractBaseApproval implements TicketApprovalInterface
{
    /**
     * Filter name used in agent UI search templates.
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
     * @param Ticket                 $ticket
     * @param EntityManagerInterface $em
     * @param ApprovalTemplate       $template
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return AbstractBaseApproval
     */
    public static function createTicketApprovalFromTemplate(
        Ticket $ticket,
        EntityManagerInterface $em,
        ApprovalTemplate $template
    ) {
        return self::createFromTemplate($em, $template, (new self())->setTicket($ticket));
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtraApproversWhenCreatingFromTemplate(
        EntityManagerInterface $em,
        SelectedApprovers $selectedApprovers
    ) {
        $approvers    = [];
        $ticketPerson = $this->getTicket()->getPerson();

        if ($selectedApprovers->hasTicketUser()) {
            $approvers[] = $ticketPerson;
        }

        if ($selectedApprovers->hasOrganizationManagers()) {
            $orgManagers = $em->getRepository(Person::class)->getOrganizationManagersForPerson($ticketPerson);
            foreach ($orgManagers as $orgManager) {
                $approvers[] = $orgManager;
            }
        }

        return $approvers;
    }

    /**
     * {@inheritdoc}
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
     *
     * @return self
     */
    public function setTicket(Ticket $ticket)
    {
        $this->setModelField('ticket', $ticket);

        return $this;
    }

    /**
     * Set ID for email template preview.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}
