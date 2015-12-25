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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService\Content;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Data\Criteria\Criteria;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Class ArticlePendingCreateDataService.
 */
class ArticlePendingCreateDataService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param Criteria $criteria
     * @param int      $page
     * @param int      $count
     *
     * @return Pagerfanta
     */
    public function selectAPC(Criteria $criteria, $page, $count)
    {
        $qb = $this->em->createQueryBuilder();

        $qb
            ->select('apc')
            ->from('DeskPRO:ArticlePendingCreate', 'apc');
        $criteria->applyFilters($qb);

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return $pager;
    }

    /**
     * @param array  $params
     * @param Person $user
     *
     * @return array
     */
    public function normalizeAssigned(array $params, Person $user)
    {
        if (array_key_exists('assigned_person', $params)) {
            if ($params['assigned_person'] === 'me') {
                $params['assigned_person'] = $user->getId();
            } elseif (!$params['assigned_person']) {
                unset($params['assigned_person']);
            }
        }

        return $params;
    }
}
