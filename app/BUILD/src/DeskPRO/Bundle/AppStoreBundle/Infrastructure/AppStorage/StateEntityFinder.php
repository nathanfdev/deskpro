<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppStorage;

use DeskPRO\Bundle\AppBundle\Entity;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use DeskPRO\Bundle\AppStoreBundle\Domain\AppStorage\AccessOptions;
use DeskPRO\Bundle\AppStoreBundle\Domain\AppStorageSearchFilter;
use Doctrine\ORM;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure;

class StateEntityFinder
{
    /** @var ORM\EntityManager */
    private $entityManager;

    /** @var Infrastructure\EntityQueryBuilders */
    private $queryBuilder;

    /** @var StateEntityConverter */
    private $entityConverter;

    public function __construct(
        ORM\EntityManager $entityManager,
        Infrastructure\EntityQueryBuilders $qb = null,
        StateEntityConverter $entityConverter = null
    ) {
        $this->entityManager = $entityManager;

        if (is_null($qb)) {
            $this->queryBuilder = new Infrastructure\EntityQueryBuilders();
        } else {
            $this->queryBuilder = $qb;
        }

        if (is_null($entityConverter)) {
            $this->entityConverter = $entityConverter;
        } else {
            $this->entityConverter = $entityConverter;
        }
    }

    /**
     * @param Domain\AppStorageItemIdentifier $identifier
     * @param AccessOptions $accessOptions
     * @param AccessRequest $request
     * @return Entity\AppStore\AppState|null
     */
    public function findOne( Domain\AppStorageItemIdentifier $identifier, Domain\AppStorage\AccessOptions $accessOptions, AccessRequest $request)
    {
        $searchFilter = AppStorageSearchFilter::fromIdentifier($identifier);
        /** @var ORM\Query[] $findStateQueries */
        $findStateQueries = [];
        if ($accessOptions->isWorldAccessible()) {
            $findStateQueries[] = $this->queryBuilder->buildFindOwnedByNobodyStateQuery($this->entityManager, $searchFilter);
        } else {
            $ownerId = $request->getAuthPersonId();
            $findStateQueries[] = $this->queryBuilder->buildFindOwnedStateQuery(
                $this->entityManager,
                $ownerId,
                $searchFilter
            );


            $accessLevel = $request->getAccessLevel();
            $accessPermission = new Domain\AppStorage\AccessPermission($accessLevel, Domain\Constants::PERMISSION_EVERYONE);
            $searchFilter->setAccessPermission($accessPermission);

            $findStateQueries[] = $this->queryBuilder->buildFindOwnedByOtherStateQuery(
                $this->entityManager,
                $ownerId,
                $searchFilter
            );
        }

        /** @var Entity\AppStore\AppState $stateEntity */
        $stateEntity = null;
        foreach ($findStateQueries as $query) {
            $result = $query->setMaxResults(2)->getResult();
            if (1 === count($result)) { //instance not found or more than one
                $stateEntity = array_pop($result);
                break;
            }

            if (2 == count($result)) {
                break;
            }
        }

        return $stateEntity;
    }
}
