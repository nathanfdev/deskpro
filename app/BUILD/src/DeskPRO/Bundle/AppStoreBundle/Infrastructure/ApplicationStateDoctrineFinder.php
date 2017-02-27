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
     * @return Entity\AppStore\AppAsset
     */
    public function find(Domain\ApplicationStateId $id)
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

        $result = $qb->getQuery()->getResult();
        if (1 != count($result)) { //instance not found or more than one
            return null;
        }

        /** @var Entity\AppStore\AppAsset $instance */
        $instance = array_pop($result);
        return $instance;
    }

    /**
     * @param Domain\SearchStateFilter $assetFilter
     * @return Domain\ApplicationState[]
     */
    public function findApplicationState(Domain\SearchStateFilter $assetFilter)
    {
        return [];
    }
}
