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
namespace DeskPRO\Bundle\AppBundle\DataService\Content\Comment;

use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\Data\Criteria\CriteriaInterface;
use DeskPRO\Bundle\AppBundle\Data\Criteria\GroupableCriteriaInterface;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Class CommentsDataService.
 */
class CommentsDataService
{
    /**
     * @var EntityManager
     */
    private $em;

    public static $datePeriodLabels = [
        'today'      => 'Today',
        'yesterday'  => 'Yesterday',
        'this_week'  => 'This Week',
        'this_month' => 'This Month',
        'last_month' => 'Last Month',
        'this_year'  => 'This Year',
        'ever'       => 'Ever',
    ];

    /**
     * PeopleDataService constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param string                     $class    Concrete comment entity class
     * @param GroupableCriteriaInterface $criteria
     *
     * @return Count
     */
    public function countComments($class, GroupableCriteriaInterface $criteria)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('count(c) as value')
            ->from($class, 'c');
        $criteria->applyFilters($qb);

        if ($criteria->hasGroupBy()) {
            $criteria->applyGroupBy($qb);

            $result    = $qb->getQuery()->getArrayResult();
            $groupedBy = $criteria->getGroupBy();
            $count     = Count::fromGroupedBy($groupedBy);
            foreach ($result as $group) {
                $count->add($group['value']);
                if ($groupedBy === 'period_created') {
                    $count->addNested(
                        $group['value'],
                        $group['group_name'],
                        $groupedBy,
                        self::$datePeriodLabels[$group['group_name']]
                    );
                } else {
                    $count->addNested($group['value'], $group['group_name'], $groupedBy, $group['group_name']);
                }
            }
        } else {
            $total = $qb->getQuery()->getSingleScalarResult();
            $count = Count::fromValue($total);
        }

        return $count;
    }

    /**
     * @param string            $class
     * @param CriteriaInterface $criteria
     * @param int               $page
     * @param int               $count
     *
     * @return Pagerfanta
     */
    public function selectComments($class, CriteriaInterface $criteria, $page, $count)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('c')
            ->from($class, 'c');
        $criteria->applyFilters($qb);

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setCurrentPage($page);
        $pager->setMaxPerPage($count);

        return $pager;
    }
}
