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

namespace DeskPRO\Bundle\ApiBundle\Controller\Feedback;

use Application\DeskPRO\Entity\Feedback;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Traits\Filters\DateFiltersTrait;
use DeskPRO\Bundle\ApiBundle\Traits\Filters\LabelFiltersTrait;
use DeskPRO\Bundle\ApiBundle\Traits\Filters\ListFiltersTrait;
use DeskPRO\Bundle\ApiBundle\Traits\Filters\QueryFilterContext;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractFeedbackController.
 */
abstract class AbstractFeedbackController extends CrudController
{
    use LabelFiltersTrait, DateFiltersTrait, ListFiltersTrait;

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
        $this->applyDateRangeFilter(
            new QueryFilterContext($qb, $alias, $request),
            'date_created', 'created_from', 'created_to'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFeedbackListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $context = new QueryFilterContext($qb, $alias, $request);
        $this->applyLabelFilters($context, Feedback::class);

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
                ->leftJoin("$alias.status_category", 'statusCategory')
                ->andWhere('statusCategory.id IN (:statusCategory)')
                ->setParameter('statusCategory', $statusCategory)
            ;
        }

        $customCategory = $request->get('custom_category');
        if (!empty($customCategory)) {
            $qb
                ->leftJoin("$alias.custom_data", 'customCat')
                ->andWhere('customCat.input IN (:custom_category)')
                ->setParameter('custom_category', $customCategory)
            ;
        }

        $this->applyInListFilter($context, 'status');
        $this->applyInListFilter($context, 'hidden_status');
    }
}
