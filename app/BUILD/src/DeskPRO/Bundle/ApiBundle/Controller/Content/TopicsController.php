<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content;

use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\Topic;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\ListHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\RequestQueryContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Form\Type\Content\TopicType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TopicsController.
 *
 * @Feature("guides")
 * @ApiModes("all")
 * @Rest\Route("/topics")
 * @ApiDoc(target="all", section="Content", output="Application\DeskPRO\Entity\Topic")
 * @ApiDoc(
 *     target="listAction,countAction",
 *     filters={
 *          {"name"="author", "dataType"="string", "pattern"="\d+|me", "description"="filter by author, provide an id or 'me' for current user"},
 *          {"name"="guide", "dataType"="integer", "pattern"="\d+|[\d+]", "description"="filter category, could be an array or just digit"},
 *          {"name"="group_by", "dataType"="string", "pattern"="author|category|period_created|period_updated", "description"="how to group downloads"},
 *          {"name"="status", "dataType"="string", "pattern"="published|archived|hidden", "description"="filter by status"},
 *          {"name"="hidden_status", "dataType"="integer", "pattern"="unpublished|deleted|spam|draft", "description"="select for article with given id"},
 *          {"name"="period_created", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by period when content was created"},
 *          {"name"="period_last_comment", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by period when content was last commented"},
 *          {"name"="period_published", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by py period when content was published"},
 *          {"name"="period_updated", "dataType"="integer", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by py period when content was updated"},
 *     }
 * )
 * @ApiDoc(
 *     target="listAction",
 *     filters={
 *          {"name"="order_by", "dataType"="integer", "pattern"="date_created|date_updated|person", "description"="how to order"}
 *     }
 * )
 * @ApiDoc(
 *     target="countAction",
 *     filters={
 *          {"name"="group_by", "dataType"="string", "pattern"="author|category|period_created|period_updated", "description"="how to group counters"}
 *     }
 * )
 * @ApiDoc(
 *     target="postAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Content\TopicType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\Topic",
 *          "person"="Application\DeskPRO\Entity\Person"
 *      }
 *     }
 * )
 */
class TopicsController extends AbstractContentController
{
    public static $entity     = Topic::class;
    public static $category   = Guide::class;
    public static $type       = TopicType::class;
    public static $exposeOnly = ['get', 'list', 'count', 'delete', 'post'];

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

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'person' => $this->getUser(),
        ]);

        return parent::handleForm($model, $request, $options);
    }
}
