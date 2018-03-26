<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class TicketPriority extends AbstractEntityRepository
{
    public function countAll()
    {
        return $this->_em->createQuery('SELECT count(p) FROM DeskPRO:TicketPriority p')->getSingleScalarResult();
    }

    public function findByTitle($title)
    {
        try {
            $priority = $this->getEntityManager()->createQuery('
                SELECT p
                FROM DeskPRO:TicketPriority p
                WHERE p.title LIKE ?1
            ')->setParameter(1, "%$title%")->getSingleResult();
        } catch (\Exception $e) {
            return;
        }

        return $priority;
    }

    /**
     * @return array
     */
    public function getNames($for_ids = null)
    {
        if ($for_ids) {
            $pris = $this->getByIds($for_ids);
        } else {
            $pris = $this->getEntityManager()->createQuery('
                SELECT p
                FROM DeskPRO:TicketPriority p
                ORDER BY p.priority
            ')->execute();
        }

        $ret = [];
        foreach ($pris as $p) {
            $ret[$p->getId()] = $p->getTitle();
        }

        return $ret;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        $pris = $this->getEntityManager()->createQuery('
            SELECT p
            FROM DeskPRO:TicketPriority p
            ORDER BY p.priority ASC
        ')->execute();

        return $pris;
    }

    /**
     * Get all priority IDs in the order they are meant to go.
     *
     * @return array
     */
    public function getIdsInOrder()
    {
        $names = $this->getNames();

        return array_keys($names);
    }
}
