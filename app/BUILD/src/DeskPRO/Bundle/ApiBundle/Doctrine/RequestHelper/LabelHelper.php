<?php

namespace DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper;

/**
 * Class LabelHelper.
 */
class LabelHelper
{
    /**
     * @param RequestQueryContext $context
     * @param string              $entityClass
     */
    public static function applyLabelFilters(RequestQueryContext $context, $entityClass)
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
                $qb2 = $qb->getEntityManager()->createQueryBuilder();
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
