<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Community;

use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\CustomDefCommunityTopic;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\CustomDataHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\DateHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\LabelHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\ListHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\RequestQueryContext;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractCommunityController.
 */
abstract class AbstractCommunityController extends CrudController
{
    /**
     * @param QueryBuilder $qb
     * @param string       $alias
     * @param Request      $request
     */
    protected function applyNotReviewedFilters(QueryBuilder $qb, $alias, Request $request)
    {
        if ($request->get('awaiting_validation')) {
            $qb->andWhere("$alias.is_reviewed = 0");
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $alias
     * @param Request      $request
     */
    protected function applyDateCreatedFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $context = new RequestQueryContext($qb, $alias, $request);
        DateHelper::applyDateRangeFilter($context, 'date_created', 'created_from', 'created_to');
    }

    /**
     * {@inheritdoc}
     */
    protected function applyCommunityListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $context = new RequestQueryContext($qb, $alias, $request);
        LabelHelper::applyLabelFilters($context, CommunityTopic::class);

        $forum = $request->get('forum');
        if (!empty($forum)) {
            $qb
                ->leftJoin("$alias.forum", 'forum')
                ->andWhere('forum.title IN (:forum_title)')
                ->setParameter('forum_title', $forum)
            ;
        }

        $statusCategory = $request->get('status_category');
        if (!empty($statusCategory)) {
            $qb
                ->leftJoin("$alias.status_category", 'status_category')
                ->andWhere('status_category.id IN (:status_category)')
                ->setParameter('status_category', $statusCategory)
            ;
        }

        $customForum = $request->get('category');
        if (!empty($customForum)) {
            $qb
                ->join("$alias.custom_data", 'customForum')
                ->join('customForum.field', 'def')
                ->andWhere('def.title IN (:category)')
                ->setParameter('category', $customForum)
            ;
        }

        ListHelper::applyInListFilter($context, 'status');
        ListHelper::applyInListFilter($context, 'hidden_status');
        CustomDataHelper::applyCustomDataFilters($context, 'community', CustomDefCommunityTopic::class);
    }
}
