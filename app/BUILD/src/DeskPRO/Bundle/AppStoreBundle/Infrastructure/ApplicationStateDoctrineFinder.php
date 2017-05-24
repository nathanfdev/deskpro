<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use DeskPRO\Bundle\AppBundle\Entity;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use Doctrine\ORM;
use Doctrine\ORM\QueryBuilder;

class ApplicationStateDoctrineFinder implements Domain\ApplicationStateFinder
{
    /** @var ORM\EntityManager */
    private $entityManager;

    public function __construct(ORM\EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Domain\ApplicationStateId $id
     * @param string|null $stateOwnerId
     * @return Entity\AppStore\AppState
     */
    public function find(Domain\ApplicationStateId $id, $stateOwnerId = null)
    {
        //TODO do not assume the application state id is the same as the persistence id
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\AppState::class, 'a')
            ->select('a')
            ->innerJoin('a.appInstance', 'i')
            ->where('a.name = :name')
            ->andWhere('i.id = :instance')
            ->setParameter('name', $id->getName())
            ->setParameter('instance', $id->getInstanceId())
        ;

        if (!is_null($stateOwnerId)) {
            $qb->andWhere('a.owner = :ownerId')->setParameter('ownerId', $stateOwnerId);
        }

        $result = $qb->getQuery()->getResult();
        if (1 != count($result)) { //instance not found or more than one
            return null;
        }

        /** @var Entity\AppStore\AppState $instance */
        $instance = array_pop($result);
        return $instance;
    }

    /**
     * @param Domain\ApplicationInstance $application
     * @param Domain\SearchStateFilter $searchFilter
     * @return Domain\ApplicationState[]
     */
    public function findApplicationStateByFilter(Domain\ApplicationInstance $application, Domain\SearchStateFilter $searchFilter)
    {
        //TODO think about a custom query builder for a domain filter to orm filter
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\AppState::class, 'a')
            ->select('a')
            ->innerJoin('a.appInstance', 'i')
            ->where('i.id = :instance')
            ->setParameter('instance', $application->getId())
        ;

        $this->applySearchStateFilter($qb, $searchFilter);
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    private function applySearchStateFilter(QueryBuilder $qb, Domain\SearchStateFilter $assetFilter)
    {
        $filterField = $assetFilter->getName();
        if (! empty($filterField)) {
            $qb->andWhere('a.name = :name')->setParameter('name', $filterField);
        }

        $scopeList = array_map(
            function(Domain\StateScope $scope){
                return Domain\StateScope::convertToString($scope);
            },
            $assetFilter->getScopeList()
        );
        if (! empty($scopeList)) {
            $qb->andWhere('a.scope IN (:scope)')->setParameter('ids', $scopeList);
        }
    }
}
