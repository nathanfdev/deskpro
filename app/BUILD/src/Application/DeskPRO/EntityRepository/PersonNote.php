<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Person as PersonEntity;

class PersonNote extends AbstractEntityRepository
{
    public function getNotesForPerson(PersonEntity $person)
    {
        return $this->getEntityManager()->createQuery('
            SELECT n
            FROM DeskPRO:PersonNote n
            WHERE n.person = ?1
            ORDER BY n.id DESC
        ')->execute([1 => $person]);
    }
}
