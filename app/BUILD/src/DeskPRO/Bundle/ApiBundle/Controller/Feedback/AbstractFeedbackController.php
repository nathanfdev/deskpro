<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Feedback;

use Application\DeskPRO\Entity\CustomDefFeedback;
use Application\DeskPRO\Entity\Feedback;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\CustomDataHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\DateHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\LabelHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\ListHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\RequestQueryContext;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractFeedbackController.
 */
abstract class AbstractFeedbackController extends CrudController
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
    protected function applyFeedbackListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $context = new RequestQueryContext($qb, $alias, $request);
        LabelHelper::applyLabelFilters($context, Feedback::class);

        $category = $request->get('category');
        if (!empty($category)) {
            $qb
                ->leftJoin("$alias.category", 'category')
                ->andWhere('category.title IN (:category_title)')
                ->setParameter('category_title', $category)
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

        $customCategory = $request->get('custom_category');
        if (!empty($customCategory)) {
            $qb
                ->join("$alias.custom_data", 'customCat')
                ->join('customCat.field', 'def')
                ->andWhere('def.title IN (:custom_category)')
                ->setParameter('custom_category', $customCategory)
            ;
        }

        ListHelper::applyInListFilter($context, 'status');
        ListHelper::applyInListFilter($context, 'hidden_status');
        CustomDataHelper::applyCustomDataFilters($context, 'feedback', CustomDefFeedback::class);
    }
}
