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

    /**
     * @param ORM\EntityManager $entityManager
     */
    public function __construct(ORM\EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $applicationName
     * @return mixed
     */
    function findSoleApplicationInstance($applicationName)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\AppInstance::class, 'i')
            ->select('i, a')
            ->innerJoin('i.app', 'a')
            ->where('a.name = :name')
            ->setMaxResults(2)
            ->setParameter('name', $applicationName)
        ;

        $result = $qb->getQuery()->getResult();
        if (1 != count($result)) { //instance not found or more than one
            return null;
        }

        /** @var Entity\AppStore\AppInstance $instance */
        $instance = array_pop($result);
        return $instance;
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

    /**
     * @param string $applicationName
     * @return Entity\AppStore\AppInstance[]
     */
    function findByApplication($applicationName) {

        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\AppInstance::class, 'i')
            ->select('i, a')
            ->innerJoin('i.app', 'a')
            ->where('a.name = :name')
            ->setParameter('name', $applicationName)
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    function findByFilter(Domain\SearchApplicationInstanceFilter $filter)
    {
        if ($filter->isEmpty()) {
            return [];
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\AppInstance::class, 'i')
            ->select('i')
            ->where('1')
            ->where('i.app.id != 1')
        ;

        if ($filter->hasScope()) {
            $qb->andWhere('i.scope = :scope')->setParameter('scope', $filter->getScope());
        }

        if ($filter->hasApplicationIdList()) {
            $qb->andWhere('i.app IN (:appIds)')->setParameter('appIds', $filter->getApplicationIdList());
        }

        $result = $qb->getQuery()->getResult();
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
