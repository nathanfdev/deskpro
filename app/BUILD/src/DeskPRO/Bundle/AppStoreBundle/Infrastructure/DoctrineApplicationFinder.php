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
use DeskPRO\Bundle\AppStoreBundle\Domain\ApplicationFinder;
use Doctrine\ORM;

class DoctrineApplicationFinder implements ApplicationFinder
{
    /** @var ORM\EntityManager */
    private $entityManager;

    public function __construct(ORM\EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param $name
     * @return mixed
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

        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    function findByInstanceId($id)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\AppInstance::class, 'i')
            ->select('i, a')
            ->innerJoin('i.app', 'a')
            ->where('i.id = :id')
            ->setParameter('id', $id)
        ;

        $result = $qb->getQuery()->getResult();
        if (1 != count($result)) { //instance not found or more than one
            return null;
        }

        /** @var Entity\AppStore\AppInstance $instance */
        $instance = array_pop($result);
        return $instance->getApp();
    }
}
