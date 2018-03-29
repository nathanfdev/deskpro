<?php

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Bundle\AppBundle\Entity\TicketFollowUp as TicketFollowUpEntity;
use Doctrine\ORM\EntityManager;

/**
 * Class TicketFollowUp.
 */
class CancelFollowUpOnUserReply implements TicketSaveActionInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
    {
        if ($ticket->getStateChangeRecorder()->isNewTicket() || !$ticket->getStateChangeRecorder()->hasNewUserReply()) {
            return;
        }

        $query = $this->em
            ->createQueryBuilder()
            ->update(TicketFollowUpEntity::class, 'f')
            ->set('f.status', ':canceled_status')
            ->where(
                'f.status = :pending_status',
                'f.cancelIfUserReply = 1',
                'f.ticket = :ticket'
            )
            ->setParameter('canceled_status', TicketFollowUpEntity::STATUS_CANCELLED)
            ->setParameter('pending_status', TicketFollowUpEntity::STATUS_PENDING)
            ->setParameter('ticket', $ticket)
            ->getQuery()
        ;

        $query->execute();
    }
}
