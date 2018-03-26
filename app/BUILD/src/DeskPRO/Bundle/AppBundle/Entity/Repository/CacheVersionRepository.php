<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use DeskPRO\Bundle\AppBundle\Entity\CacheVersion as CacheVersionEntity;
use Doctrine\ORM\EntityRepository;

/**
 * Class CacheVersion.
 */
class CacheVersionRepository extends EntityRepository
{
    /**
     * @param $resource_id
     *
     * @return null|CacheVersionEntity
     */
    public function findByResourceId($resource_id)
    {
        return $this->findOneBy(['resource_id' => $resource_id]);
    }
}
