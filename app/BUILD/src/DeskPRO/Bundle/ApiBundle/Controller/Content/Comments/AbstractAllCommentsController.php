<?php

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
