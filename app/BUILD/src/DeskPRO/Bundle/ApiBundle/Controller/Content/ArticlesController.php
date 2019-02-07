<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Content\ArticleType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ArticlesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/articles")
 * @ApiDoc(target="all", section="Content", output="Application\DeskPRO\Entity\Article")
 * @ApiDoc(
 *     target="listAction,countAction",
 *     filters={
 *          {"name"="author", "dataType"="string", "pattern"="\d+|me", "description"="filter by author, provide an id or 'me' for current user"},
 *          {"name"="category", "dataType"="integer", "pattern"="\d+|[\d+]", "description"="filter category, could be an array or just digit"},
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
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Content\ArticleType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\Article"
 *      }
 *     }
 * )
 */
class ArticlesController extends AbstractContentController
{
    public static $entity   = Article::class;
    public static $category = ArticleCategory::class;
    public static $type     = ArticleType::class;

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        parent::applyListFilters($qb, $alias, $request);

        $category = $request->get('category');
        $brands   = $request->query->get('brands');

        if ($category || $brands) {
            $qb->leftJoin("$alias.categories", 'cat');
        }

        if ($category) {
            $qb->andWhere('cat.id IN (:category)');
            $qb->setParameter('category', $category);
        }
        if ($brands) {
            $qb->andWhere('cat.brand IN (:brand_ids)');
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
                ->leftJoin("$alias.categories", 'cat')
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
        $noClean = false;
        if ($request->request->get('no_clean')) {
            $noClean = true;
            $request->request->remove('no_clean');
        }
        $options = array_merge($options, [
            'person'       => $this->getUser(),
            'filter_clean' => !($this->getUser()->isAdmin() && $noClean),
        ]);

        return parent::handleForm($model, $request, $options);
    }
}
