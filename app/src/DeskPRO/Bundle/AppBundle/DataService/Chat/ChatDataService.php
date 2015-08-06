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

namespace DeskPRO\Bundle\AppBundle\DataService\Chat;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\QueryException;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\CountBadge\CountsGroup;
use DeskPRO\Bundle\AppBundle\CountBadge\GroupedCount;

/**
 * Class ChatDataService
 */
class ChatDataService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * ChatDataService constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param ChatSelectCriteria $criteria
     * @param int $page
     * @param int $count
     * @return Pagerfanta
     */
    public function selectChats(ChatSelectCriteria $criteria, $page, $count)
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select('c')
           ->from('DeskPRO:ChatConversation', 'c');
        $criteria->applyFilters($qb);

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return $pager;
    }

    /**
     * @param ChatCountCriteria $criteria
     * @return Count
     */
    public function countChats(ChatCountCriteria $criteria)
    {
        return $criteria->isGrouped() ? $this->countGrouped($criteria) : $this->countFlat($criteria);
    }

    /**
     * @param ChatCountCriteria $criteria
     * @return Count
     */
    private function countFlat(ChatCountCriteria $criteria)
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select('count(c)')
           ->from('DeskPRO:ChatConversation', 'c');
        $criteria->applyFilters($qb);

        try {
            $count = $qb->getQuery()->getSingleScalarResult();
        } catch (QueryException $e) {
            $count = 0;
        }

        return new Count($count);
    }

    /**
     * @param ChatCountCriteria $criteria
     * @return Count
     */
    private function countGrouped(ChatCountCriteria $criteria)
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select('count(c) as value')
           ->from('DeskPRO:ChatConversation', 'c');
        $criteria->applyFilters($qb);
        $criteria->applyGroupBy($qb);

        $result = $qb->getQuery()->getArrayResult();

        $count = 0;
        $nested = new CountsGroup($criteria->getGroupBy());
        foreach ($result as $group) {
            $count += $group['value'];
            $nested->add(new GroupedCount($group['group_name'], $group['value']));
        }

        return new Count($count, $nested);
    }
}