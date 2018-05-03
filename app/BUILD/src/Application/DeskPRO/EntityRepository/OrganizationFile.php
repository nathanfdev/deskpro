<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Organization as OrganizationEntity;

class OrganizationFile extends AbstractEntityRepository
{
    public function getFilesForOrganization(OrganizationEntity $org)
    {
        return $this->getEntityManager()->createQuery('
            SELECT f
            FROM DeskPRO:OrganizationFile f
            WHERE f.organization = ?1
            ORDER BY f.id DESC
        ')->execute([1 => $org]);
    }
}
