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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Model;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Exception\UnknownTicketFlagException;
use Doctrine\ORM\EntityManager;

/**
 * Pseudo-implementation of ticket flags. Those were hard-coded so this makes coupling a
 * little more loose.
 */
class TicketStatuses
{
    protected $statuses = [];

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;

        $this->statuses = [
            Ticket::STATUS_AWAITING_AGENT,
            Ticket::STATUS_AWAITING_USER,
            Ticket::STATUS_RESOLVED,
            Ticket::STATUS_ARCHIVED,
            Ticket::STATUS_HIDDEN,
        ];
    }

    protected function getEm()
    {
        return $this->em;
    }

    /**
     * Get the list of available flags.
     *
     * @return array[string] the list of all ticket flag names
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

    public function isStatusValid($status)
    {
        return in_array($status, $this->getStatuses());
    }

    /**
     * Gets all the tickets matching a flag.
     *
     * @param string the status name.
     *
     * @throws UnknownTicketFlagException
     *
     * @return a list of tickets.
     */
    public function getAllTicketsForStatus($status)
    {
        return $this->getEm()->getRepository('DeskPRO:Ticket')->findBy(array(
            'status' => $status,
        ));
    }
}
