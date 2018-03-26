<?php

namespace DeskPRO\Bundle\ApiBundle\Traits\Tickets;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Serializer\OffsetList;
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
     * @param int   $offset
     * @param int   $maxPerPage
     *
     * @return OffsetList
     */
    public function getTicketsOffsetList($total, array $ids, $offset, $maxPerPage)
    {
        return new OffsetList($this->getTicketsByIdsQuery($ids), $maxPerPage, $offset, $total, false);
    }

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
        $tickets = $this->getTicketsByIdsQuery($ids)->getQuery()->getResult();
        $pager   = new Pagerfanta(new FixedAdapter($total, $tickets));

        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($currentPage);

        return $pager;
    }

    /**
     * @param array $ids
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getTicketsByIdsQuery(array $ids)
    {
        $qb = $this->getManager()->createQueryBuilder();
        $qb
            ->select('t, field(t.id, :ids) as HIDDEN field')
            ->from(Ticket::class, 't')
            ->where('t.id IN (:ids)')
            ->orderBy('field')
            ->setParameter('ids', $ids)
        ;

        return $qb;
    }
}
