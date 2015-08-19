<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService\Content;

use Doctrine\ORM\EntityManagerInterface as EntityManager;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\CountBadge\CountsGroup;
use DeskPRO\Bundle\AppBundle\CountBadge\GroupedCount;
use DeskPRO\Bundle\AppBundle\Data\Criteria\GroupedCriteria;

/**
 * Class ContentCountsDataService
 */
class ContentCountsDataService
{
    /**
     * @var EntityManager
     */
    private $em;

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
     * @param GroupedCriteria $criteria
     * @return Count
     */
    public function countContent($class, GroupedCriteria $criteria)
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select('count(c) as value')
           ->from($class, 'c');
        $criteria->applyFilters($qb);
        $criteria->applyGroupBy($qb);

        $result = $qb->getQuery()->getArrayResult();

        $count = 0;
        $nested = new CountsGroup($criteria->getGroupBy());
        foreach ($result as $group) {
            $count += $group['value'];
            $nested->add(new GroupedCount($group['group_name'], $group['value']));
        }

        // if $count above isn't a sum of distinct results, then need to perform additional query
        if (!$criteria->isGroupByDistinct()) {
            $totalQb = $this->em->createQueryBuilder();
            $totalQb->select('count(distinct c)')
                    ->from($class, 'c');
            $criteria->applyFilters($totalQb);
            $count = $totalQb->getQuery()->getSingleScalarResult();
        }

        return new Count($count, $nested);
    }
}