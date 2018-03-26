<?php

namespace DeskPRO\Bundle\AppBundle\DataService\Tickets;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Doctrine\ORM\EntityManager;

/**
 * Class TicketExcerptDataService.
 */
class TicketExcerptDataService
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
     * @param int[]|Ticket[] $tickets
     * @param bool           $includeNote
     *
     * @return array
     */
    public function getTicketsLastReply($tickets, $includeNote = false)
    {
        // e.g. convert pagerfanta to a simple array
        if ($tickets instanceof \Traversable) {
            $ticketsArray = [];
            foreach ($tickets as $ticket) {
                $ticketsArray[] = $ticket;
            }

            $tickets = $ticketsArray;
        }

        $qb2 = $this->em->createQueryBuilder();
        $qb2
            ->select('MAX(m2.id) AS id')
            ->from(TicketMessage::class, 'm2')
            ->where('m2.ticket IN(:ids)')
            ->setParameter('ids', $tickets)
            ->groupBy('m2.ticket')
        ;

        if (!$includeNote) {
            $qb2->andWhere('m2.is_agent_note = 0');
        }

        $messageIds = $qb2->getQuery()->getResult();

        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('m')
            ->from(TicketMessage::class, 'm')
            ->where('m.id IN(:ids)')
            ->setParameter('ids', $messageIds)
        ;

        /** @var TicketMessage[] $result */
        $result   = $qb->getQuery()->getResult();
        $messages = [];

        foreach ($result as $message) {
            $messages[$message->getTicket()->getId()] = $message;
        }

        return $messages;
    }
}
