<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class PersonField extends AbstractEntityRepository
{
    /**
     * @return array
     */
    public function getEnabledFields()
    {
        return $this->_em->createQuery('
            SELECT f
            FROM DeskPRO:PersonField f
            WHERE f.parent IS NULL
        ')->execute();
    }
}
