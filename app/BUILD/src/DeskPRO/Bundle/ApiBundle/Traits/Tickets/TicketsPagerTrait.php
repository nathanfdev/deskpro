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

namespace DeskPRO\Bundle\ApiBundle\Traits\Tickets;

use Application\DeskPRO\Entity\Ticket;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Class TicketsPagerTrait.
 *
 * @method EntityManager getManager()
 */
trait TicketsPagerTrait
{
    /**
     * @param int   $total
     * @param array $ids
     * @param int   $currentPage
     * @param int   $maxPerPage
     *
     * @return Pagerfanta
     */
    public function getTicketsPager($total, array $ids, $currentPage, $maxPerPage)
    {
        $tickets = [];
        if (count($ids)) {
            $qb = $this->getManager()->createQueryBuilder();
            $qb
                ->select('t, field(t.id, :ids) as HIDDEN field')
                ->from(Ticket::class, 't')
                ->where('t.id IN (:ids)')
                ->orderBy('field')
                ->setParameter('ids', $ids)
            ;

            $tickets = $qb->getQuery()->getResult();
        }

        $pager = new Pagerfanta(new FixedAdapter($total, $tickets));

        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($currentPage);

        return $pager;
    }
}
