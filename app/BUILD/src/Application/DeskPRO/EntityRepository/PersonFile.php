<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Person as PersonEntity;

class PersonFile extends AbstractEntityRepository
{
    public function getFilesForPerson(PersonEntity $person)
    {
        return $this->getEntityManager()->createQuery('
            SELECT f
            FROM DeskPRO:PersonFile f
            WHERE f.person = ?1
            ORDER BY f.id DESC
        ')->execute([1 => $person]);
    }
}
