<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\Entity\Ticket;
use Doctrine\ORM\EntityRepository;

class TicketFeedbackLinkRepository extends EntityRepository
{
    public function findByTicketAndJoinFeedbackData(Ticket $ticket)
    {
        $qb = $this->createQueryBuilder('tfl');
        $qb
            ->select('tfl', 'feedback', 'category', 'status_category')
            ->leftJoin('tfl.feedback', 'feedback')
            ->leftJoin('feedback.category', 'category')
            ->leftJoin('feedback.status_category', 'status_category')
            ->where('tfl.ticket = :ticket')
            ->setParameter('ticket', $ticket)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Ticket $ticket
     *
     * @return array
     */
    public function getFeedbackIdsByTicket(Ticket $ticket)
    {
        $feedbackLinks = $this->findByTicket($ticket);

        return array_map(function ($feedbackLink) {
            return $feedbackLink->getFeedback()->getId();
        }, $feedbackLinks);
    }
}
