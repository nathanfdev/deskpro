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
        $createdFrom = $request->get('created_from');
        if ($createdFrom) {
            $qb
                ->andWhere("$alias.date_created >= DATE(:from_date)")
                ->setParameter('from_date', $createdFrom)
            ;
        }

        $createdTo = $request->get('created_to');
        if ($createdTo) {
            $qb
                ->andWhere("$alias.date_created <= DATE(:to_date)")
                ->setParameter('to_date', $createdTo)
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFeedbackListFilters(QueryBuilder $qb, $alias, Request $request)
    {
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

        $label      = $request->get('label');
        $labelsMode = $request->get('labels_mode');

        if (!empty($label)) {
            // cast to array
            $label = (array) $label;

            if ($labelsMode === 'all') {
                $qb2 = $this->getManager()->createQueryBuilder();
                $qb2
                    ->select('f2.id')
                    ->from(Feedback::class, 'f2')
                    ->join('f2.labels', 'labels')
                    ->where('labels.label IN (:labels)')
                    ->groupBy('f2.id')
                    ->having('COUNT(f2.id) = :label_count')
                ;

                $qb
                    ->andWhere("$alias.id IN ({$qb2->getDQL()})")
                    ->setParameter('labels', $label)
                    ->setParameter('label_count', count($label))
                ;
            } else {
                $qb
                    ->leftJoin("$alias.labels", 'labels')
                    ->andWhere('labels.label IN (:labels)')
                    ->setParameter('labels', $label)
                ;
            }
        } elseif ($request->get('no_labels')) {
            $qb
                ->leftJoin("$alias.labels", 'labels')
                ->andWhere('labels.label IS NULL')
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

        $status = $request->get('status');
        if (!empty($status)) {
            $qb
                ->andWhere("$alias.status IN (:status)")
                ->setParameter('status', $status)
            ;
        }

        $hiddenStatus = $request->get('hidden_status');
        if (!empty($hiddenStatus)) {
            $qb
                ->andWhere("$alias.hidden_status = :hidden_status")
                ->setParameter('hidden_status', $hiddenStatus)
            ;
        }
    }
}
