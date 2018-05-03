<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content;

use Application\DeskPRO\Entity\CategoryAbstract;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\DateHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\ListHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\RequestQueryContext;
use DeskPRO\Bundle\AppBundle\CountBadge\AbstractCount;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractContentController.
 */
abstract class AbstractContentController extends CrudController
{
    public static $category;
    public static $sortOptions = [
        'id'           => 'id',
        'date_created' => 'date_created',
        'date_updated' => 'date_updated',
        'person'       => 'person',
        'status'       => 'status',
        'title'        => 'title',
    ];

    /**
     * Get data for export to CSV.
     *
     * @Rest\Get("/csv")
     * @SerializerView(mapping={
     *     "Application\DeskPRO\Entity\Article": "DeskPRO\Bundle\AppBundle\Serializer\Model\Content\ArticleCsv",
     *     "Application\DeskPRO\Entity\News": "DeskPRO\Bundle\AppBundle\Serializer\Model\Content\ContentCsv",
     *     "Application\DeskPRO\Entity\Download": "DeskPRO\Bundle\AppBundle\Serializer\Model\Content\ContentCsv",
     * })
     *
     * @param Request $request
     *
     * @return \FOS\RestBundle\View\View
     */
    public function csvAction(Request $request)
    {
        return $this->listAction($request);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $context = new RequestQueryContext($qb, $alias, $request);

        ListHelper::applyInListFilter($context, 'status');
        ListHelper::applyInListFilter($context, 'hidden_status');
        ListHelper::applyInListFilter($context, 'person', 'author');

        DateHelper::applyDatePeriodFilter($context, 'date_created', 'period_created');
        DateHelper::applyDatePeriodFilter($context, 'date_updated', 'period_updated');
        DateHelper::applyDatePeriodFilter($context, 'date_published', 'period_published');
        DateHelper::applyDatePeriodFilter($context, 'date_last_comment', 'period_last_comment');
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
                    ->groupBy('group_name');

                break;
            case 'period_created':
                $context = new RequestQueryContext($qb, $alias, $request);
                DateHelper::applyDatePeriodGroupBy($context, 'date_created');

                break;
            case 'period_updated':
                $context = new RequestQueryContext($qb, $alias, $request);
                DateHelper::applyDatePeriodGroupBy($context, ['date_created', 'date_updated']);

                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function addGroupByNestedCounts(AbstractCount $count, array $result)
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

                $nestedCount = $count::create($value, $id, $count->getGroupedBy(), $category->getTitle(), $groupBy);
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
