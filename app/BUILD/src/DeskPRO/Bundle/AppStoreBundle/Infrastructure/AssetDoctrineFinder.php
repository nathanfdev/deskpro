<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use DeskPRO\Bundle\AppStoreBundle\Domain;
use Doctrine\ORM;
use DeskPRO\Bundle\AppBundle\Entity;

class AssetDoctrineFinder implements Domain\AssetFinder
{
    /** @var ORM\EntityManager */
    private $entityManager;

    public function __construct(ORM\EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Domain\Application $application
     * @return Entity\AppStore\AppAssetBlob[]
     */
    function findAllApplicationAssets(Domain\Application $application)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\AppAssetBlob::class, 'asset')
            ->select('asset')
            ->innerJoin('asset.app', 'app')
            ->innerJoin('asset.blob', 'blob')
            ->where('app = :app')
            ->setParameter('app', $application->getId())
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param Domain\Application $application
     * @param Domain\SearchAssetFilter $assetFilter
     * @return Domain\ApplicationAsset[]
     */
    function findApplicationAssets(Domain\Application $application, Domain\SearchAssetFilter $assetFilter)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\AppAssetBlob::class, 'asset')
            ->select('asset')
            ->innerJoin('asset.app', 'app')
            ->where('app = :app')
            ->setParameter('app', $application->getId())
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }
}
