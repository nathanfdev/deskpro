<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util;

use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Exists;
use Elastica\Query\Range;
use Elastica\Query\Term;
use Elastica\Query\Terms;

/**
 * Class ElasticQueryUtils.
 */
class ElasticQueryUtils
{
    /**
     * @param string $fieldColumn
     * @param string $operator
     * @param mixed  $checkValue
     * @param bool   $handleNull
     *
     * @throws \InvalidArgumentException
     *
     * @return AbstractQuery|false
     */
    public static function buildQuery($fieldColumn, $operator, $checkValue = null, $handleNull = false)
    {
        $checkValue = ValueFormatter::formatCheckValue($checkValue);

        switch ($operator) {
            case Query::OP_EQ:
                return new Term([$fieldColumn => $checkValue]);
            case Query::OP_NEQ:
                $term = new BoolQuery();
                $term->addMustNot(new Term([$fieldColumn => $checkValue]));

                if ($handleNull) {
                    $term->addMustNot(new Exists($fieldColumn));
                }

                return $term;
            case Query::OP_LT:
                return new Range($fieldColumn, [
                    'lt' => $checkValue,
                ]);
            case Query::OP_LTE:
                return new Range($fieldColumn, [
                    'lte' => $checkValue,
                ]);
            case Query::OP_GT:
                return new Range($fieldColumn, [
                    'gt' => $checkValue,
                ]);
            case Query::OP_GTE:
                return new Range($fieldColumn, [
                    'gte' => $checkValue,
                ]);
            case Query::OP_IN:
            case Query::OP_HAS:
                return new Terms($fieldColumn, ValueFormatter::formatList($checkValue));
            case Query::OP_NOT_IN:
                $term = new BoolQuery();
                $term->addMustNot(new Terms($fieldColumn, ValueFormatter::formatList($checkValue)));

                if ($handleNull) {
                    $term->addMustNot(new Exists($fieldColumn));
                }

                return $term;
            case Query::OP_BETWEEN:
                $checkValue = ValueFormatter::formatRange($checkValue);
                if (!$checkValue) {
                    return false;
                }

                return new Range($fieldColumn, [
                    'gte' => $checkValue[0],
                    'lte' => $checkValue[1],
                ]);
            case Query::OP_NOT_BETWEEN:
                $checkValue = ValueFormatter::formatRange($checkValue);
                if (!$checkValue) {
                    return false;
                }

                $term = new BoolQuery();
                $term->addMustNot(new Range($fieldColumn, [
                    'gte' => $checkValue[0],
                    'lte' => $checkValue[1],
                ]));

                if ($handleNull) {
                    $term->addMustNot(new Exists($fieldColumn));
                }

                return $term;
            case Query::OP_IS_NULL:
            case Query::OP_EMPTY:
            case Query::OP_NOT_EXISTS:
                $term = new BoolQuery();
                $term->addMustNot(new Exists($fieldColumn));

                return $term;
            case Query::OP_NOT_NULL:
            case Query::OP_NOT_EMPTY:
            case Query::OP_EXISTS:
                return new Exists($fieldColumn);
            default:
                throw new \InvalidArgumentException("Unknown operator: {$operator}");
        }
    }
}
