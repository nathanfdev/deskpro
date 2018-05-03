<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class Product extends AbstractCategoryRepository
{
    public function countAll()
    {
        return $this->_em->createQuery('SELECT count(p) FROM DeskPRO:Product p')->getSingleScalarResult();
    }

    public function getAll()
    {
        $products = $this->getEntityManager()->createQuery('
            SELECT p, ch
            FROM DeskPRO:Product p
            LEFT JOIN p.children ch
            ORDER BY p.display_order ASC
        ')->execute();

        return $products;
    }
}
