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
use DeskPRO\Bundle\AppStoreBundle\Domain\Application;
use DeskPRO\Bundle\AppStoreBundle\Domain\ApplicationFinder;
use DeskPRO\Bundle\AppStoreBundle\Domain\SearchApplicationInstanceFilter;
use Doctrine\ORM;

class ApplicationDoctrineFinder implements ApplicationFinder
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

    function findAll()
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\App::class, 'a')
            ->select('a')
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    function findAllById($idList)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\App::class, 'a')
            ->select('a')
            ->where('a.id IN (:idList)')->setParameter('idList', $idList)
        ;

        $result = $qb->getQuery()->getResult();
        return $result;

    }

    /**
     * @param string $name
     * @return Entity\AppStore\App|null
     */
    function findByName($name)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\App::class, 'a')
            ->select('a')
            ->where('a.name = :name')
            ->setParameter('name', $name)
        ;

        $result = $qb->getQuery()->getResult();
        if (1 != count($result)) { //instance not found or more than one
            return null;
        }

        /** @var Entity\AppStore\App $instance */
        $instance = array_pop($result);
        return $instance;
    }

    /**
     * @param string $id
     * @return Entity\AppStore\App|null
     */
    function findByInstanceId($id)
    {
        $query = $this->queryBuilder->buildFindApplicationByInstanceIdQuery($this->entityManager, $id);
        $result = $query->setMaxResults(2)->getResult();

        /** @var Entity\AppStore\App $instance */
        $instance = null;
        if (1 === count($result)) { // must find exactly one
            $instance = array_pop($result);
        }

        return $instance;
    }
}
