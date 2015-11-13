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
namespace DeskPRO\Bundle\AppBundle\DataService\Tasks;

use DeskPRO\Bundle\AppBundle\Data\Criteria\Criteria;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Class TasksDataService.
 */
class TasksDataService extends AbstractTasksDataService
{
    /**
     * Select filtered list of tasks.
     *
     * @param Criteria $criteria
     * @param int      $page
     * @param int      $count
     *
     * @return Pagerfanta
     */
    public function selectTasks(Criteria $criteria, $page, $count)
    {
        $qb = $this->getBaseQueryBuilder();
        $qb
            ->select('t')
            ->leftJoin('t.assigned', 'ta')
            ->leftJoin('t.project', 'p')
            ->leftJoin('t.labels', 'l')
            ->addGroupBy('t.id')
        ;

        $criteria->applyFilters($qb);

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
