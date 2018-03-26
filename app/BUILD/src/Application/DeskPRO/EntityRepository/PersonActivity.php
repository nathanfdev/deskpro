<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Organization as OrganizationEntity;
use Application\DeskPRO\Entity\Person as PersonEntity;

class PersonActivity extends AbstractEntityRepository
{
    public function getForPerson(PersonEntity $person, $max = 30, $offset = 0)
    {
        return $this->getEntityManager()->createQuery('
            SELECT a
            FROM DeskPRO:PersonActivity a
            WHERE a.person = ?1
            ORDER BY a.id DESC
        ')->setMaxResults($max)->setFirstResult($offset)->execute([1 => $person['id']]);
    }

    public function countForPerson(PersonEntity $person)
    {
        return $this->getEntityManager()->getConnection()->fetchColumn('
            SELECT COUNT(*)
            FROM person_activity
            WHERE person_id = ?
        ', [$person->id]);
    }

    public function getForOrganization(OrganizationEntity $org, $max = 30, $offset = 0)
    {
        return $this->getEntityManager()->createQuery('
            SELECT a
            FROM DeskPRO:PersonActivity a
            LEFT JOIN a.person p
            WHERE p.organization = ?1
            ORDER BY a.id DESC
        ')->setMaxResults($max)->setFirstResult($offset)->execute([1 => $org['id']]);
    }

    public function countForOrganization(OrganizationEntity $org)
    {
        return $this->getEntityManager()->getConnection()->fetchColumn('
            SELECT COUNT(*)
            FROM people
            INNER JOIN person_activity AS act ON (act.person_id = people.id)
            WHERE people.organization_id = ?
        ', [$org->id]);
    }
}
