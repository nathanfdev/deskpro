<?php

namespace DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper;

/**
 * Class FieldHelper.
 */
class SearchHelper
{
    /**
     * @param RequestQueryContext $context
     * @param string              $field
     * @param array|string        $fields
     */
    public static function applyFieldFilter(RequestQueryContext $context, $field, $fields)
    {
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        $qb    = $context->getQb();
        $alias = $context->getAlias();
        $value = $context->getRequest()->get($field);

        if (!empty($value)) {
            $orX = $qb->expr()->orX();

            foreach ($fields as $f) {
                $orX->add($qb->expr()->like("$alias.$f", ':'.$field));
            }
            $qb->andWhere($orX);
            $qb->setParameter($field, '%'.addcslashes($value, '%_').'%');
        }
    }
}
