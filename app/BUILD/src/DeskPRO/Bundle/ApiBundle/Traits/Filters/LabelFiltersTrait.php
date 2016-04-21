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

namespace DeskPRO\Bundle\ApiBundle\Traits\Filters;

use Doctrine\ORM\EntityManager;

/**
 * Trait LabelFiltersTrait.
 *
 * @method EntityManager getManager()
 */
trait LabelFiltersTrait
{
    /**
     * @param QueryFilterContext $context
     * @param string             $entityClass
     */
    protected function applyLabelFilters(QueryFilterContext $context, $entityClass)
    {
        $qb      = $context->getQb();
        $alias   = $context->getAlias();
        $request = $context->getRequest();

        $label      = $request->get('label');
        $labelsMode = $request->get('labels_mode');

        if (!empty($label)) {
            // cast to array
            $label = (array) $label;

            if ($labelsMode === 'all') {
                $qb2 = $this->getManager()->createQueryBuilder();
                $qb2
                    ->select('labelSubQuery.id')
                    ->from($entityClass, 'labelSubQuery')
                    ->join('labelSubQuery.labels', 'labels')
                    ->where('labels.label IN (:labels)')
                    ->groupBy('labelSubQuery.id')
                    ->having('COUNT(labelSubQuery.id) = :label_count')
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
    }
}
