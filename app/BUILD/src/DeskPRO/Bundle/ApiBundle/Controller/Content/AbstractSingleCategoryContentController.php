<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content;

use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\ListHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\RequestQueryContext;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractSingleCategoryContentController.
 */
abstract class AbstractSingleCategoryContentController extends AbstractContentController
{
    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        parent::applyListFilters($qb, $alias, $request);
        ListHelper::applyInListFilter(new RequestQueryContext($qb, $alias, $request), 'category');

        $brands = $request->query->get('brands');
        if ($brands) {
            $qb->join("$alias.category", 'category');
            $qb->andWhere('category.brand IN (:brand_ids)');
            $qb->setParameter('brand_ids', $brands);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListGroupBy(QueryBuilder $qb, $alias, $groupBy, Request $request)
    {
        if ($groupBy === 'category') {
            $qb
                ->addSelect('cat.id as group_name')
                ->addSelect('cat.title as title')
                ->leftJoin("$alias.category", 'cat')
                ->groupBy('group_name')
            ;
        } else {
            parent::applyListGroupBy($qb, $alias, $groupBy, $request);
        }
    }
}
