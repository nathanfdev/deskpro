<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util;

use Carbon\Carbon;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\Util\ListUtils;
use Orb\Util\Dates;

/**
 * Class CheckValueUtils.
 */
class CheckValueUtils
{
    /**
     * @param mixed          $fieldValue
     * @param string         $operator
     * @param mixed|OptValue $checkValue
     *
     * @throws \InvalidArgumentException
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
                $checkValue = ValueFormatter::formatList($checkValue);

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
                $result = function($flag) use ($operator) {
                    return ($operator === Query::OP_NOT_BETWEEN) ? !$flag : $flag;
                };

                if (count($checkValue) !== count(array_filter($checkValue))) {
                    return false;
                }

                if ($fieldValue instanceof \DateTime) {
                    if (Dates::isArrayOfDateObjects($checkValue)) {
                        return $result(Carbon::instance($fieldValue)->between(
                            Carbon::instance($checkValue[0]),
                            Carbon::instance($checkValue[1])
                        ));
                    } else {
                        return false;
                    }
                } else {
                    return $result($fieldValue >= $checkValue[0] && $fieldValue <= $checkValue[1]);
                }

            case Query::OP_IS_NULL:
            case Query::OP_NOT_NULL:
                $result = function ($flag) use ($operator) {
                    return ($operator === Query::OP_NOT_NULL) ? !$flag : $flag;
                };

                if (is_null($fieldValue)) {
                    return $result(true);
                }

                if (is_string($fieldValue)) {
                    return $result(trim($fieldValue) === '');
                }

                if (is_numeric($fieldValue)) {
                    return $result($fieldValue === 0 || $fieldValue === '0');
                }

                if ($fieldValue instanceof \Countable) {
                    return $result(count($fieldValue) === 0);
                }

                break;
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
