<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Entity\TicketCommunityTopicLink;
use Doctrine\ORM\EntityRepository;

class TicketCommunityTopicLinkRepository extends EntityRepository
{
    public function findByTicketAndJoinCommunityTopicData(Ticket $ticket)
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
    public function getCommunityTopicIdsByTicket(Ticket $ticket)
    {
        /** @var TicketCommunityTopicLink[] $communityTopicLinks */
        $communityTopicLinks = $this->findByTicket($ticket);

        return array_map(function ($communityTopicLink) {
            /* @var TicketCommunityTopicLink $communityTopicLink */
            return $communityTopicLink->getTopic()->getId();
        }, $communityTopicLinks);
    }
}
