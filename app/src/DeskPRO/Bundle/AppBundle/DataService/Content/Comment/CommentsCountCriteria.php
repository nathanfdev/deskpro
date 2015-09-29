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

use DeskPRO\Bundle\AppBundle\Data\Criteria\Groupable;
use DeskPRO\Bundle\AppBundle\Data\Criteria\GroupableCriteriaInterface;
use DeskPRO\Bundle\AppBundle\Data\DatePeriods;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CommentsCountCriteria.
 */
class CommentsCountCriteria extends CommentsSelectCriteria implements GroupableCriteriaInterface
{
    use Groupable;

    /**
     * {@inheritdoc}
     */
    public function getGroupByAllowedValues()
    {
        return ['article', 'news', 'download', 'status', 'period_created'];
    }

    /**
     * {@inheritdoc}
     */
    public function applyGroupBy(QueryBuilder $qb)
    {
        $this->ensureGroupBy();

        $alias = $qb->getRootAliases()[0];
        switch ($this->group_by) {
            case 'article':
            case 'news':
            case 'download':
                $qb->addSelect('p.id as group_name');
                $qb->leftJoin("$alias.$this->group_by", 'p');
                break;

            case 'status':
                $qb->addSelect("$alias.status as group_name");
                break;

            case 'period_created':
                $datePeriodCaseWhen = DatePeriods::getDatePeriodCaseWhenDql("$alias.date_created");
                $qb->addSelect("$datePeriodCaseWhen as group_name");
                break;
        }

        $qb->groupBy('group_name');
    }
}
