<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class TicketCategory extends AbstractCategoryRepository implements TicketBuiltInFieldInterface
{
    public function countAll()
    {
        return $this->_em->createQuery('SELECT count(c) FROM DeskPRO:TicketCategory c')->getSingleScalarResult();
    }

    public function getAll()
    {
        return $this->getCategories();
    }

    public function getCategories()
    {
        return $this->_em->createQuery('
            SELECT c, ch
            FROM DeskPRO:TicketCategory c
            LEFT JOIN c.children ch
            ORDER BY c.display_order ASC
        ')->execute();
    }
}
