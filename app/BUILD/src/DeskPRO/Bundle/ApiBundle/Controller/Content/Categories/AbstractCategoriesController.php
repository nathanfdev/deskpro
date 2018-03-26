<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content\Categories;

use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\ListHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\RequestQueryContext;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractCategoriesController.
 */
abstract class AbstractCategoriesController extends CrudController
{
    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        ListHelper::applyInListFilter(new RequestQueryContext($qb, $alias, $request), 'brand', 'brands');
        ListHelper::applyInListFilter(new RequestQueryContext($qb, $alias, $request), 'parent');
    }
}
