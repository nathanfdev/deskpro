<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\EntityRepository\AbstractEntityRepository;
use DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ApprovalRepository
 *
 * @package DeskPRO\Bundle\AppBundle\Entity\Repository
 */
class ApprovalRepository extends AbstractEntityRepository
{
    /**
     * @param AbstractBaseApproval $approval
     * @return Person[]
     */
    public function getApproversFromApproval(AbstractBaseApproval $approval)
    {
        return $this->buildGetApproversFromApproval($approval)
            ->getQuery()
            ->getResult()
        ;
    }

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
     * @param AbstractBaseApproval $approval
     * @return QueryBuilder
     */
    private function buildGetApproversFromApproval(AbstractBaseApproval $approval)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('p, pe')
            ->from(Person::class, 'p')
            ->innerJoin('p.primary_email', 'pe')
            ->andWhere('p.is_deleted = FALSE AND p.is_disabled = FALSE')
            ->andWhere('p.id IN (:approverIds)')
            ->setParameter('approverIds', $approval->getApprovers(), Connection::PARAM_INT_ARRAY)
        ;
    }
}
