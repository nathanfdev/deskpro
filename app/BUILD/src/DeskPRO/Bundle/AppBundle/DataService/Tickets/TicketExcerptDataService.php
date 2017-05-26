<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
            ->groupBy('m2.ticket')
        ;

        if (!$includeNote) {
            $qb2->andWhere('m2.is_agent_note = 0');
        }

        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('m')
            ->from(TicketMessage::class, 'm')
            ->where("m.id IN({$qb2->getDQL()})")
            ->setParameter('ids', $tickets)
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
