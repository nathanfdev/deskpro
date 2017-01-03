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

namespace DeskPRO\Bundle\ApiBundle\Controller\Content\Comments;

use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\DateHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\ListHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\RequestQueryContext;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractAllCommentsController.
 */
abstract class AbstractAllCommentsController extends CrudController
{
    public static $contentType;
    public static $exposeOnly  = ['get', 'list', 'count', 'delete'];
    public static $sortOptions = [
        'date_created' => 'date_created',
        'person'       => 'person',
    ];

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $context = new RequestQueryContext($qb, $alias, $request);
        DateHelper::applyDatePeriodFilter($context, 'date_created', 'period_created');
        ListHelper::applyInListFilter($context, 'status');
        ListHelper::applyInListFilter($context, static::$contentType);

        $isReviewed = $request->get('is_reviewed');
        if ($request->query->has('is_reviewed')) {
            $qb->andWhere("$alias.is_reviewed = :is_reviewed");
            $qb->setParameter('is_reviewed', (int) $isReviewed);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListGroupBy(QueryBuilder $qb, $alias, $groupBy, Request $request)
    {
        switch ($groupBy) {
            case 'status':
                $qb
                    ->addSelect("$alias.status as group_name")
                    ->addSelect("$alias.status as title")
                    ->groupBy('group_name')
                ;

                break;
            case 'period_created':
                $context = new RequestQueryContext($qb, $alias, $request);
                DateHelper::applyDatePeriodGroupBy($context, 'date_created');

                break;
            case static::$contentType:
                $qb
                    ->addSelect('p.title as title')
                    ->addSelect('p.id as group_name')
                    ->leftJoin("$alias.$groupBy", 'p')
                    ->groupBy('group_name')
                ;

                break;
        }
    }
}
