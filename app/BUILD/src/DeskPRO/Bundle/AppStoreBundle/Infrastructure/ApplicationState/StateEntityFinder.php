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

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\ApplicationState;

use DeskPRO\Bundle\AppBundle\Entity;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use Doctrine\ORM;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure;

class StateEntityFinder implements Domain\ApplicationStateFinder
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
     * @param Domain\ApplicationStateId $id
     * @param string|null $stateOwnerId
     * @return Domain\ApplicationState
     */
    public function find(Domain\ApplicationStateId $id, $stateOwnerId = null)
    {
        $query = $this->queryBuilder->buildFindStateEntityByIdQuery($this->entityManager, $id, $stateOwnerId);
        $result = $query->getResult();
        if (1 != count($result)) { //instance not found or more than one
            return null;
        }

        /** @var Entity\AppStore\AppState $instance */
        $instance = array_pop($result);
        return $this->entityConverter->toDomainObject($instance);
    }

    /**
     * @param Domain\ApplicationStateSearchFilter $searchFilter
     * @return Domain\ApplicationState[]
     */
    public function findByFilter(Domain\ApplicationStateSearchFilter $searchFilter)
    {
        $query = $this->queryBuilder->buildFindStateEntityByFilterQuery($this->entityManager, $searchFilter);
        $result = $query->getResult();

        $mappedResults = [];
        foreach ($result as $entity) { // ignore
            $domainObject = $this->entityConverter->toDomainObject($entity);
            if ($domainObject) {
                $mappedResults[] = $domainObject;
            }
        }
        return $mappedResults;
    }
}
