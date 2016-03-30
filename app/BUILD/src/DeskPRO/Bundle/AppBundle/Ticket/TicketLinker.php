<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Ticket;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\EntityRepository\Ticket as TicketRepository;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\LinkedTickets;
use Doctrine\ORM\EntityManager;

/**
 * Class TicketLinker.
 */
class TicketLinker
{
    const LINK_TYPE_PARENT  = 'parent';
    const LINK_TYPE_CHILD   = 'child';
    const LINK_TYPE_SIBLING = 'sibling';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * TicketLinker constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param Ticket $ticket
     * @param Ticket $link_ticket
     */
    public function linkTickets(Ticket $ticket, Ticket $link_ticket)
    {
        $ticket->parent_ticket = $link_ticket;
        $this->em->flush($ticket);
    }

    /**
     * @param $ticket_id
     * @param $unlink_ticket_id
     * @param $link_type
     */
    public function unlinkTickets($ticket_id, $unlink_ticket_id, $link_type)
    {
        switch ($link_type) {
            case self::LINK_TYPE_CHILD:
            case self::LINK_TYPE_SIBLING:
                $to_unlink = $unlink_ticket_id;
                break;

            case self::LINK_TYPE_PARENT:
                $to_unlink = $ticket_id;
                break;
            default:
                throw new \InvalidArgumentException(
                    sprintf(
                        'Wrong link type, expecting one of [ %s ], but [ %s ] given',
                        implode(',', [self::LINK_TYPE_PARENT, self::LINK_TYPE_CHILD, self::LINK_TYPE_SIBLING]),
                        $link_type
                    ),
                    400
                );

        }
        $to_unlink_ticket = $this->getTicketRepo()->find($to_unlink);
        if (!$to_unlink_ticket) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Ticket with [ %d ] was not found',
                    $to_unlink
                ),
                404
            );
        }
        $to_unlink_ticket->parent_ticket = null;
        $this->em->flush($to_unlink_ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return array
     */
    public function getLinkedTickets(Ticket $ticket)
    {
        $siblings = $this->getTicketSiblings($ticket);
        $children = $this->getTicketChildren($ticket);

        return new LinkedTickets(
            $ticket->getParentTicket() ?: null,
            array_values($siblings),
            array_values($children),
            array_sum([$ticket->getParentTicket() ? 1 : 0, count($siblings), count($children)])
        );
    }

    /**
     * @param Ticket $ticket
     *
     * @return array
     */
    public function getTicketSiblings(Ticket $ticket)
    {
        $parent = $ticket->getParentTicket();

        return $parent
            ? array_filter(
                $this->getTicketRepo()->getLinkedTickets($parent),
                function (Ticket $item) use ($ticket) {
                    return $item->getId() !== $ticket->getId();
                }
            )
            : [];
    }

    /**
     * @param Ticket $ticket
     *
     * @return array
     */
    public function getTicketChildren(Ticket $ticket)
    {
        return $this->getTicketRepo()->getLinkedTickets($ticket);
    }

    /**
     * @return TicketRepository
     */
    private function getTicketRepo()
    {
        return $this->em->getRepository('\Application\DeskPRO\Entity\Ticket');
    }
}
