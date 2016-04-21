<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller\Content;

use Application\DeskPRO\Entity\CategoryAbstract;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Traits\Filters\DateFiltersTrait;
use DeskPRO\Bundle\ApiBundle\Traits\Filters\ListFiltersTrait;
use DeskPRO\Bundle\ApiBundle\Traits\Filters\QueryFilterContext;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\Data\DatePeriods;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractContentController.
 */
abstract class AbstractContentController extends CrudController
{
    use ListFiltersTrait, DateFiltersTrait;

    public static $category;
    public static $exposeOnly  = ['get', 'list', 'count', 'delete'];
    public static $sortOptions = [
        'id'           => 'id',
        'date_created' => 'date_created',
        'date_updated' => 'date_updated',
        'person'       => 'person',
    ];

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $context = new QueryFilterContext($qb, $alias, $request);

        $this->applyInListFilter($context, 'status');
        $this->applyInListFilter($context, 'hidden_status');
        $this->applyInListFilter($context, 'person', 'author');

        $this->applyDatePeriodFilter($context, 'date_created', 'period_created');
        $this->applyDatePeriodFilter($context, 'date_updated', 'period_updated');
        $this->applyDatePeriodFilter($context, 'date_published', 'period_published');
        $this->applyDatePeriodFilter($context, 'date_last_comment', 'period_last_comment');
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListGroupBy(QueryBuilder $qb, $alias, $groupBy, Request $request)
    {
        switch ($groupBy) {
            case 'author':
                $qb
                    ->addSelect('p.id as group_name')
                    ->addSelect('p.name as title')
                    ->leftJoin("$alias.person", 'p')
                    ->groupBy('group_name')
                ;

                break;
            case 'period_created':
                $periodDql = DatePeriods::getDatePeriodCaseWhenDql("$alias.date_created");
                $qb->addSelect("$periodDql as group_name");
                $qb->addSelect("$periodDql as title");
                $qb->groupBy('group_name');

                break;
            case 'period_updated':
                $periodDql = DatePeriods::getDatePeriodCaseWhenDql("COALESCE($alias.date_updated, $alias.date_created)");
                $qb->addSelect("$periodDql as group_name");
                $qb->addSelect("$periodDql as title");
                $qb->groupBy('group_name');

                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function addGroupByNestedCounts(Count $count, array $result)
    {
        if ($count->getGroupedBy() === 'category') {
            $map = [];
            foreach ($result as $item) {
                $map[$item['group_name']] = $item['value'];
            }

            $categories = $this->getManager()->getRepository(static::$category)->findBy(['parent' => null]);
            $addNested  = function (Count $count, CategoryAbstract $category) use ($map, &$addNested) {
                $id       = $category->getId();
                $children = $category->getChildren();
                $value    = isset($map[$id]) ? $map[$id] : 0;
                $groupBy  = !$children->count() ? $count->getGroupedBy() : '';

                $nestedCount = Count::create($value, $id, $count->getGroupedBy(), $category->getTitle(), $groupBy);
                foreach ($children as $childCategory) {
                    $addNested($nestedCount, $childCategory);
                }

                $count->addNestedInstance($nestedCount, true);
            };

            foreach ($categories as $category) {
                $addNested($count, $category);
            }
        } else {
            parent::addGroupByNestedCounts($count, $result);
        }
    }
}
