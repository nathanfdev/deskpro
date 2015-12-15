<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Notification;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\ORM\EntityManager;
use DeskPRO\Bundle\AppBundle\Entity\ActionAlert;

/**
 * Class NotificationService.
 */
class NotificationService
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getLastActionAlerts($last, Person $user)
    {
        $actionAlertRepo = $this->em->getRepository('App:ActionAlert');
        /** @var ActionAlert $last */
        $last   = $actionAlertRepo->findOneBy(['uuid' => $last]);
        $qb     = $actionAlertRepo->createQueryBuilder('aa');
        $result = $qb->where('aa.date_created > (:last)')
            ->andWhere('aa.target_id = :target_id')
            ->setParameter('last', $last->getDateCreated())
            ->setParameter('target_id', $user->getId())
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function lastAlert(Person $user)
    {
        $actionAlertRepo = $this->em->getRepository('App:ActionAlert');
        $last            = $actionAlertRepo->findBy(['target_id' => $user->getId()], ['id' => 'DESC']);
        if ($last) {
            return array_shift($last);
        }
    }
}
