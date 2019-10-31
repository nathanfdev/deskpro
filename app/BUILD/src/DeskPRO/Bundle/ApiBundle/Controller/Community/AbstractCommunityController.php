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

        $channel = $request->get('channel');
        if (!empty($channel)) {
            $qb
                ->leftJoin("$alias.channel", 'channel')
                ->andWhere('channel.title IN (:channel_title)')
                ->setParameter('channel_title', $channel)
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

        $customChannel = $request->get('category');
        if (!empty($customChannel)) {
            $qb
                ->join("$alias.custom_data", 'customChan')
                ->join('customChan.field', 'def')
                ->andWhere('def.title IN (:category)')
                ->setParameter('category', $customChannel)
            ;
        }

        ListHelper::applyInListFilter($context, 'status');
        ListHelper::applyInListFilter($context, 'hidden_status');
        CustomDataHelper::applyCustomDataFilters($context, 'community', CustomDefCommunityTopic::class);
    }
}
