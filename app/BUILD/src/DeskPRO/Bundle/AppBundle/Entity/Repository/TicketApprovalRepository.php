<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\EntityRepository\AbstractEntityRepository;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;

/**
 * Class TicketApprovalRepository
 *
 * @package DeskPRO\Bundle\AppBundle\Entity\Repository
 */
class TicketApprovalRepository extends AbstractEntityRepository
{
    /**
     * @param Ticket $ticket
     * @param ApprovalTemplate $template
     * @return TicketApproval[]
     */
    public function getTicketApprovalsByTicketAndTemplate(Ticket $ticket, ApprovalTemplate $template)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('ta')
            ->from(TicketApproval::class, 'ta')
            ->andWhere('ta.ticket = :ticket')
            ->andWhere('ta.template = :template')
            ->setParameters([
                'ticket' => $ticket,
                'template' => $template,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param Ticket $ticket
     * @return TicketApproval[]
     */
    public function getTicketApprovalsByTicket(Ticket $ticket)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('ta')
            ->from(TicketApproval::class, 'ta')
            ->andWhere('ta.ticket = :ticket')
            ->setParameters([
                'ticket' => $ticket,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param Ticket $ticket
     * @param Person $approver
     * @return array
     */
    public function getTicketApprovalsByTicketAndApprover(Ticket $ticket, Person $approver)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('ta')
            ->from(TicketApproval::class, 'ta')
            ->join('ta.approvers', 'a')
            ->andWhere('ta.ticket = :ticket')
            ->andWhere('a = :approver')
            ->setParameters([
                'ticket' => $ticket,
                'approver' => $approver,
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
