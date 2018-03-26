<?php

namespace DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper;

/**
 * Class FieldHelper.
 */
class FieldHelper
{
    /**
     * @param RequestQueryContext $context
     * @param string              $field
     */
    public static function applyFieldFilter(RequestQueryContext $context, $field)
    {
        $qb    = $context->getQb();
        $alias = $context->getAlias();
        $value = $context->getRequest()->get($field);

        if (!empty($value)) {
            $qb->andWhere($qb->expr()->eq("$alias.$field", ':'.$field));
            $qb->setParameter($field, $value);
        }
    }
}
