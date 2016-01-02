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

namespace DeskPRO\Bundle\AppBundle\DataService\Feedback;

use DeskPRO\Bundle\AppBundle\Data\Criteria\Groupable;
use DeskPRO\Bundle\AppBundle\Data\Criteria\GroupableCriteriaInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FeedbackCountCriteria.
 *
 * This class uses the same filtering criteria as FeedbackSelectCriteria and additionally adds GROUP BY functionality
 */
class FeedbackCountCriteria extends FeedbackSelectCriteria implements GroupableCriteriaInterface
{
    use Groupable;

    /**
     * {@inheritdoc}
     */
    public function getGroupByAllowedValues()
    {
        return ['status_category', 'hidden_status', 'category', 'custom_category'];
    }

    /**
     * @param QueryBuilder $qb
     *
     * @throws \LogicException
     */
    public function applyGroupBy(QueryBuilder $qb)
    {
        $alias = $qb->getRootAliases()[0];
        /* @ToDo  Temporary solution before refactoring of the feedback statuses */
        if ($this->group_by === 'hidden_status') {
            $qb
                ->addSelect("{$alias}.hidden_status as group_name")
                ->addSelect("{$alias}.hidden_status as id")
                ->andWhere("{$alias}.hidden_status IS NOT NULL")
                ->andWhere("{$alias}.hidden_status <> ''");
        } elseif ($this->group_by === 'custom_category') {
            $qb
                ->addSelect('g.input as group_name')
                ->addSelect('g.id as id')
                ->leftJoin("{$alias}.custom_data", 'g')
                ->leftJoin('g.field', 'def')
                ->andWhere('def.sys_name = :cat')
                ->setParameter('cat', 'cat');
        } elseif ($this->group_by === 'category') {
            $qb
                ->addSelect('g.title as group_name')
                ->addSelect('g.id as id')
                ->innerJoin("{$alias}.category", 'g');
        } elseif ($this->group_by === 'status_category') {
            $qb
                ->leftJoin("{$alias}.{$this->group_by}", 'g')
                ->addSelect('g.title as group_name')
                ->addSelect('g.id as id')
                ->andWhere('g.status_type = :type')
                ->setParameter('type', $this->filters['status']);
        }
        $qb->groupBy('group_name');
    }
}
