<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\Entity\Ticket;
use Doctrine\ORM\EntityRepository;

class TicketCommunityTopicLinkRepository extends EntityRepository
{
    public function findByTicketAndJoinFeedbackData(Ticket $ticket)
    {
        $qb = $this->createQueryBuilder('tctl');
        $qb
            ->select('tctl', 'topic', 'channel', 'status_category')
            ->leftJoin('tctl.topic', 'topic')
            ->leftJoin('topic.channel', 'channel')
            ->leftJoin('topic.status_category', 'status_category')
            ->where('tctl.ticket = :ticket')
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
