<?php

namespace DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper;

use Application\DeskPRO\Entity\CustomDefAbstract;
use DeskPRO\Component\Util\RegexUtils;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class CustomDataHelper.
 */
class CustomDataHelper
{
    /**
     * @param RequestQueryContext $context
     * @param string              $prefix
     * @param string              $defClass
     */
    public static function applyCustomDataFilters(RequestQueryContext $context, $prefix, $defClass)
    {
        foreach ($context->getRequest()->query->all() as $param => $value) {
            $defId = self::getCustomDefId($param, $prefix);
            if ($defId) {
                /** @var CustomDefAbstract $def */
                $def = $context->getQb()->getEntityManager()->getRepository($defClass)->find($defId);
                if (!$def) {
                    throw new BadRequestHttpException("Custom field with $defId doesn't exist.");
                }

                self::addCustomDataFilter($context, $def, $value);
            }
        }
    }

    /**
     * @param string $param
     * @param string $prefix
     *
     * @return bool
     */
    public static function getCustomDefId($param, $prefix)
    {
        $fullPrefix = $prefix.'_field_';

        if (strpos($param, $fullPrefix) !== 0) {
            return false;
        }
        if (RegexUtils::safePregMatch('/^\d+$/', substr($param, strlen($fullPrefix)), $matches)) {
            return (int) $matches[0];
        }

        return false;
    }

    /**
     * @param RequestQueryContext $context
     * @param CustomDefAbstract   $def
     * @param mixed               $value
     */
    public static function addCustomDataFilter(RequestQueryContext $context, CustomDefAbstract $def, $value)
    {
        $dataAlias = 'custom_data'.$def->getId();
        $defAlias  = 'custom_def'.$def->getId();

        $defIdPlaceholder = 'def_id'.$def->getId();
        $valuePlaceholder = 'custom_data_value'.$def->getId();

        $qb = $context->getQb();
        $qb
            ->join("{$context->getAlias()}.custom_data", $dataAlias)
            ->join("$dataAlias.root_field", $defAlias, Join::WITH, "$defAlias.id = :$defIdPlaceholder")
            ->setParameter($defIdPlaceholder, $def->getId())
        ;

        if ($def->isDateType()) {
            if (isset($value['from']) || isset($value['to'])) {
                $minValue = strtotime(isset($value['from']) ? $value['from'] : null);
                $maxValue = strtotime(isset($value['to']) ? $value['to'] : null);

                if ($minValue) {
                    $qb->andWhere("$dataAlias.value >= :{$dataAlias}_min_value");
                    $qb->setParameter($dataAlias.'_min_value', $minValue);
                }

                if ($maxValue) {
                    $qb->andWhere("$dataAlias.value <= :{$dataAlias}_max_value");
                    $qb->setParameter($dataAlias.'_max_value', $maxValue);
                }
            } elseif (is_scalar($value)) {
                $valueRequest = new Request(['value' => $value]);
                $valueContext = new RequestQueryContext($context->getQb(), $dataAlias, $valueRequest);
                DateHelper::applyDatePeriodFilter($valueContext, 'value', 'value', 'timestamp');
            } else {
                throw new BadRequestHttpException("Date period expected to be a string or contains 'from' and 'to' params.");
            }
        } else {
            $qb->setParameter($valuePlaceholder, $value);

            if ($def->getWidgetType() === CustomDefAbstract::TYPE_TOGGLE) {
                $qb->andWhere("$dataAlias.value = :$valuePlaceholder");
            } elseif ($def->isChoiceType()) {
                $qb->andWhere("$dataAlias.field IN(:$valuePlaceholder)");
            } elseif (in_array($def->getWidgetType(), [CustomDefAbstract::TYPE_TEXT, CustomDefAbstract::TYPE_TEXTAREA])) {
                $qb->andWhere("$dataAlias.input = :$valuePlaceholder");
            } else {
                $qb->andWhere("CASE WHEN $dataAlias.value > 0 THEN $dataAlias.value ELSE $dataAlias.input END = :$valuePlaceholder");
            }
        }
    }
}
