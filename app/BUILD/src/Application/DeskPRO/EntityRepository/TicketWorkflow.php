<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class TicketWorkflow extends AbstractEntityRepository
{
    public function countAll()
    {
        return $this->_em->createQuery('SELECT count(w) FROM DeskPRO:TicketWorkflow w')->getSingleScalarResult();
    }

    public function getAll()
    {
        $works = $this->getEntityManager()->createQuery('
            SELECT w
            FROM DeskPRO:TicketWorkflow w
            ORDER BY w.display_order ASC
        ')->execute();

        return $works;
    }

    public function getNames($for_ids = null)
    {
        if ($for_ids) {
            $works = $this->getByIds($for_ids);
        } else {
            $works = $this->getEntityManager()->createQuery('
                SELECT w
                FROM DeskPRO:TicketWorkflow w
                ORDER BY w.display_order ASC
            ')->execute();
        }

        $ret = [];
        foreach ($works as $w) {
            $ret[$w->getId()] = $w->getTitle();
        }

        return $ret;
    }
}
