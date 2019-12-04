<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\Entity\Blob;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;

class ThemeSetAssetRepository extends EntityRepository
{
    public function findEmailAssetsEager($type)
    {
        $qb = $this->createQueryBuilder('t');
        $qb->andWhere('t.tags = :tags')->setParameter('tags', [$type]);
        $qb->innerJoin(Blob::class, 'b');

        return $qb->getQuery()
            ->setFetchMode(Blob::class, 'blob', ClassMetadata::FETCH_EAGER)
            ->getResult();
    }

    public function getCustomIcons()
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->where('t.tags LIKE :tag OR t.tags LIKE :tag2')
            ->setParameter('tag', 'custom_icon,%')
            ->setParameter('tag2', 'custom_icon')
        ;

        $qb->innerJoin(Blob::class, 'b');

        return $qb->getQuery()
            ->setFetchMode(Blob::class, 'blob', ClassMetadata::FETCH_EAGER)
            ->getResult();
    }
}
