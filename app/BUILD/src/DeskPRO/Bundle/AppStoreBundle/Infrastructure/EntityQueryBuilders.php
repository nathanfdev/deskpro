<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use DeskPRO\Bundle\AppBundle\Entity;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use Doctrine\ORM;

class EntityQueryBuilders
{
    /**
     * @param ORM\EntityManager $em
     * @param {string} $instanceId
     * @return ORM\Query
     */
    public function buildFindApplicationByInstanceIdQuery(ORM\EntityManager $em, $instanceId)
    {
        $qb = $em->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\AppInstance::class, 'i')
            ->from(Entity\AppStore\App::class, 'a')
            ->select('a')
            ->where('i.app = a.id')
            ->andWhere('i.id = :id')
            ->setParameter('id', $instanceId)
            ->groupBy('a')
        ;

        return $qb->getQuery();
    }

    /**
     * @param ORM\EntityManager $em
     * @param Domain\SearchApplicationInstanceFilter $filter
     * @return ORM\Query
     * @throws \DomainException
     */
    public function buildFindApplicationInstanceByFilterQuery(
        ORM\EntityManager $em,
        Domain\SearchApplicationInstanceFilter $filter
    ) {
        if ($filter->isEmpty()) {
            throw new \DomainException('filter is empty');
        }

        $qb = $em->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\AppInstance::class, 'i')
            ->select('i')
            ->innerJoin('i.app', 'a')
        ;

        if ($filter->hasScope()) {
            $qb->andWhere('i.scope = :scope')->setParameter('scope', $filter->getScope());
        }

        if ($filter->hasApplicationIdList()) {
            $qb->andWhere('i.app IN (:appIds)')->setParameter('appIds', $filter->getApplicationIdList());
        }

        if ($filter->hasIsDev()) {
            $qb->andWhere('a.isDev = :isDev')->setParameter('isDev', $filter->getIsDev());
        }

        if ($filter->hasIsInstalled()) {
            $qb->andWhere('i.isInstalled = :isInstalled')->setParameter('isInstalled', $filter->getIsInstalled());
        }

        return $qb->getQuery();
    }

    /**
     * @param ORM\EntityManager $em
     * @param $id
     * @return ORM\Query
     */
    public function buildFindApplicationInstanceByAppId(ORM\EntityManager $em, $id)
    {
        $qb = $em->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\AppInstance::class, 'i')
            ->select('i, a')
            ->innerJoin('i.app', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery();
    }

    /**
     * @param ORM\EntityManager $em
     * @param $applicationName
     * @return ORM\Query
     */
    public function buildFindApplicationInstanceByNameQuery(ORM\EntityManager $em, $applicationName)
    {
        $qb = $em->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\AppInstance::class, 'i')
            ->select('i, a')
            ->innerJoin('i.app', 'a')
            ->where('a.name = :name')
            ->setParameter('name', $applicationName)
        ;

        return $qb->getQuery();
    }

    /**
     * @param ORM\EntityManager $em
     * @param Domain\AppStorageSearchFilter $searchFilter
     * @return ORM\Query
     */
    public function buildFindOwnedByNobodyStateQuery(ORM\EntityManager $em, Domain\AppStorageSearchFilter $searchFilter) {

        $qb = $em->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\AppState::class, 'a')
            ->select('a')
            ->innerJoin('a.appInstance', 'i')
            ->where('a.appInstance = :application')
            ->andWhere('a.owner IS NULL')
            ->setParameter('application', $searchFilter->getApplicationInstanceId())
        ;

        $this->applySearchStateFilter($qb, $searchFilter);

        return $qb->getQuery();
    }

    /**
     * @param ORM\EntityManager $em
     * @param null $stateOwnerId
     * @param Domain\AppStorageSearchFilter $searchFilter
     * @return ORM\Query
     */
    public function buildFindOwnedStateQuery(ORM\EntityManager $em, $stateOwnerId, Domain\AppStorageSearchFilter $searchFilter) {

        $qb = $em->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\AppState::class, 'a')
            ->select('a')
            ->innerJoin('a.appInstance', 'i')
            ->where('a.appInstance = :application')
            ->andWhere('a.owner = :ownerId')
            ->setParameter('ownerId', $stateOwnerId)
            ->setParameter('application', $searchFilter->getApplicationInstanceId())
        ;

        $this->applySearchStateFilter($qb, $searchFilter);

        return $qb->getQuery();
    }

    public function buildFindOwnedByOtherStateQuery(
        ORM\EntityManager $em,
        $stateOwnerId,
        Domain\AppStorageSearchFilter $searchFilter
    ) {

        $qb = $em->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\AppState::class, 'a')
            ->select('a')
            ->innerJoin('a.appInstance', 'i')
            ->where('a.appInstance = :application')
            ->andWhere('(a.owner IS NOT NULL AND a.owner <> :ownerId)')
            ->setParameter('ownerId', $stateOwnerId)
            ->setParameter('application', $searchFilter->getApplicationInstanceId())
        ;

        $this->applySearchStateFilter($qb, $searchFilter);

        return $qb->getQuery();
    }

    /**
     * @param ORM\EntityManager $em
     * @param Domain\AppStorageItemIdentifier $id
     * @param null|string $stateOwnerId
     * @return ORM\Query
     */
    public function buildFindStateEntityByIdQuery(
        ORM\EntityManager $em,
        Domain\AppStorageItemIdentifier $id, $stateOwnerId = null
    ) {
        //TODO do not assume the application state id is the same as the persistence id
        $qb = $em->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\AppState::class, 'a')
            ->select('a')
            ->innerJoin('a.appInstance', 'i')
            ->where('a.name = :name')
            ->andWhere('a.appInstance = :instance')
            ->andWhere('a.entityId = :entityId')
            ->setParameter('name', $id->getName())
            ->setParameter('instance', $id->getInstanceId())
            ->setParameter('entityId', $id->getEntityId())
        ;

        if (!is_null($stateOwnerId)) {
            $qb->andWhere('a.owner = :ownerId')->setParameter('ownerId', $stateOwnerId);
        }

        return $qb->getQuery();
    }

    /**
     * @param ORM\EntityManager $em
     * @param Domain\AppStorageSearchFilter $searchFilter
     * @return ORM\Query
     */
    public function buildFindStateEntityByFilterQuery(
        ORM\EntityManager $em,
        Domain\AppStorageSearchFilter $searchFilter
    ) {
        $qb = $em->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\AppState::class, 'a')
            ->select('a')
            ->innerJoin('a.appInstance', 'i')
            ->where('i.id = :application')
            ->setParameter('application', $searchFilter->getApplicationInstanceId())
        ;

        $this->applySearchStateFilter($qb, $searchFilter);
        return $qb->getQuery();
    }

    private function applySearchStateFilter(ORM\QueryBuilder $qb, Domain\AppStorageSearchFilter $assetFilter)
    {
        $filterField = $assetFilter->getName();
        if (! empty($filterField)) {
            $qb->andWhere('a.name IN (:name)')->setParameter('name', $filterField);
        }

        $filterField = $assetFilter->getEntityId();
        if (! empty($filterField)) {
            $qb->andWhere('a.entityId = :entityId')->setParameter('entityId', $filterField);
        }

        $accessPermission = $assetFilter->getAccessPermision();
        if (! is_null($accessPermission)) {
            $permission = $accessPermission->getPermission();
            if ($accessPermission->hasAccessLevel(Domain\Constants::ACCESS_LEVEL_READ)) {
                $qb->andWhere('a.permRead = :permRead')->setParameter('permRead', $permission);
            } elseif ($accessPermission->hasAccessLevel(Domain\Constants::ACCESS_LEVEL_WRITE)) {
                $qb->andWhere('a.permWrite = :permWrite')->setParameter('permWrite', $permission);
            } else {
                $msg = sprintf('Unknown access permission:  level: %s and permission: %s ', $accessPermission->getAccessLevel(), $permission);
                throw new \DomainException($msg);
            }
        }
    }
}
