<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Organization as OrganizationEntity;

class OrganizationNote extends AbstractEntityRepository
{
    public function getNotesForOrganization(OrganizationEntity $org)
    {
        return $this->getEntityManager()->createQuery('
            SELECT n
            FROM DeskPRO:OrganizationNote n
            WHERE n.organization = ?1
            ORDER BY n.id DESC
        ')->execute([1 => $org]);
    }
}
