<?php

namespace DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper;

/**
 * Class ListHelper.
 */
class ListHelper
{
    /**
     * @param RequestQueryContext $context
     * @param string              $property
     * @param string|null         $queryParam
     */
    public static function applyInListFilter(RequestQueryContext $context, $property, $queryParam = null)
    {
        $queryParam = $queryParam ?: $property;
        $value      = $context->getRequest()->get($queryParam);

        if ($value) {
            $context->getQb()->andWhere("{$context->getAlias()}.$property IN (:$queryParam)");
            $context->getQb()->setParameter($queryParam, $value);
        }
    }
}
