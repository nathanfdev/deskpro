<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util;

use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\Util\ListUtils;

class CheckValueUtils
{
    /**
     * @param mixed          $fieldValue
     * @param string         $operator
     * @param mixed|OptValue $checkValue
     *
     * @return bool
     */
    public static function checkValue($fieldValue, $operator, $checkValue)
    {
        if ($checkValue instanceof OptValue) {
            $checkValue = $checkValue->getValue();
        }

        switch ($operator) {
            case Query::OP_EQ:
                return $fieldValue == $checkValue;

            case Query::OP_NEQ:
                return $fieldValue != $checkValue;

            case Query::OP_LT:
                return $fieldValue < $checkValue;

            case Query::OP_LTE:
                return $fieldValue <= $checkValue;

            case Query::OP_GT:
                return $fieldValue > $checkValue;

            case Query::OP_GTE:
                return $fieldValue >= $checkValue;

            case Query::OP_IN:
            case Query::OP_HAS:
            case Query::OP_NOT_IN:
                if ($checkValue === null) {
                    $checkValue = [];
                }
                if ($checkValue === 0 || $checkValue === '0') {
                    $checkValue = [];
                }
                if (!is_array($checkValue)) {
                    $checkValue = [$checkValue];
                }

                $checkValue = ListUtils::flatten($checkValue);

                if (is_array($fieldValue)) {
                    $res = ListUtils::containsAny($fieldValue, $checkValue, false);
                } else {
                    $res = in_array($fieldValue, $checkValue);
                }

                if ($operator === Query::OP_NOT_IN) {
                    $res = !$res;
                }

                return $res;

            case Query::OP_BETWEEN:
            case Query::OP_NOT_BETWEEN:
                if (empty($checkValue[0]) || empty($checkValue[1])) {
                    return false;
                }

                $res = $fieldValue >= $checkValue[0] and $fieldValue <= $checkValue[1];

                if ($operator === Query::OP_NOT_BETWEEN) {
                    $res = !$res;
                }

                return $res;

            case Query::OP_IS_NULL:
            case Query::OP_NOT_NULL:
                $res = $fieldValue === null || $fieldValue === 0 || $fieldValue === '0';

                if ($operator === Query::OP_NOT_NULL) {
                    $res = !$res;
                }

                return $res;

            case Query::OP_EMPTY:
            case Query::OP_NOT_EMPTY:
                $res = empty($fieldValue);

                if ($operator === Query::OP_NOT_EMPTY) {
                    $res = !$res;
                }

                return $res;

            case Query::OP_EXISTS:
            case Query::OP_NOT_EXISTS:
                $res = !empty($fieldValue);

                if ($operator === Query::OP_NOT_EXISTS) {
                    $res = !$res;
                }

                return $res;

            default:
                throw new \InvalidArgumentException("Unknown operator: {$operator}");
        }
    }
}
