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

class ApplicationInstanceDoctrineFinder implements Domain\ApplicationInstanceFinder
{
    /** @var ORM\EntityManager */
    private $entityManager;

    /** @var EntityQueryBuilders */
    private $queryBuilder;

    /**
     * @param ORM\EntityManager $entityManager
     * @param EntityQueryBuilders $queryBuilder
     */
    public function __construct(ORM\EntityManager $entityManager, EntityQueryBuilders $queryBuilder = null)
    {
        $this->entityManager = $entityManager;
        if (is_null($queryBuilder)) {
            $this->queryBuilder = new EntityQueryBuilders();
        } else {
            $this->queryBuilder = $queryBuilder;
        }
    }

    public function findSoleApplicationInstanceByRef( ApplicationRef $appRef) {
        $query = null;
        $id = $appRef->getIdentifier();
        if ($appRef->isName()) {
            $query = $this->queryBuilder->buildFindApplicationInstanceByNameQuery($this->entityManager, $id);
        } else {
            $query = $this->queryBuilder->buildFindApplicationInstanceByAppId($this->entityManager, $id);
        }

        $result = $query->setMaxResults(2)->getResult();

        /** @var Entity\AppStore\AppInstance $instance */
        $instance = null;
        if (1 === count($result)) { // must find exactly one
            $instance = array_pop($result);
        }

        return $instance;
    }

    /**
     * @param string $applicationName
     * @return mixed
     */
    function findSoleApplicationInstance($applicationName)
    {
        return $this->findSoleApplicationInstanceByRef(new ApplicationRef($applicationName, true));
    }

    function findAll()
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\AppInstance::class, 'i')
            ->select('i')
            ->innerJoin('i.app', 'a')
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    function findByFilter(Domain\SearchApplicationInstanceFilter $filter)
    {
        if ($filter->isEmpty()) {
            return [];
        }

        $query = $this->queryBuilder->buildFindApplicationInstanceByFilterQuery($this->entityManager, $filter);
        $result = $query->getResult();
        return $result;
    }

    /**
     * @param string $id
     * @return mixed
     */
    function findById($id)
    {
        $repository = $this->entityManager->getRepository(Entity\AppStore\AppInstance::class);
        /** @var Entity\AppStore\AppInstance $instance */
        $instance = $repository->find($id);
        return $instance;
    }
}
