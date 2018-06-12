<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util;

use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlCondition;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\Util\ListUtils;
use Doctrine\DBAL\Connection;

class SqlQueryUtils
{
    /**
     * @param $fieldColumn
     * @param $operator
     * @param $checkValue
     * @param $handleNull
     *
     * @return SqlCondition
     */
    public static function buildQueryCondition($fieldColumn, $operator, $checkValue, $cond = null, $handleNull = false)
    {
        $where = self::buildQueryWhere($fieldColumn, $operator, $checkValue, $handleNull);

        if (!$cond) {
            $cond = new SqlCondition();
        }
        $cond->setWhere($where['where']);
        if (!empty($where['params'])) {
            foreach ($where['params'] as $name => $info) {
                $cond->setParam($name, $info[0], $info[1]);
            }
        }

        return $cond;
    }

    /**
     * @param string         $fieldColumn
     * @param string         $operator
     * @param mixed|OptValue $checkValue
     *
     * @return array Array of ['where' => XXX, 'params' => ['x' => ['val', type]]]
     */
    public static function buildQueryWhere($fieldColumn, $operator, $checkValue, $handleNull = false)
    {
        if ($checkValue instanceof OptValue) {
            $checkValue = $checkValue->getValue();
        }

        if ($checkValue instanceof \DateTime) {
            $tmp = clone $checkValue;
            $tmp->setTimezone(new \DateTimeZone('UTC'));
            $checkValue = $tmp->format('Y-m-d H:i:s');
        }

        $colVarName = preg_replace('/[^a-zA-Z0-9]/', '', $fieldColumn);

        switch ($operator) {
            case Query::OP_EQ:
                if ($checkValue === null) {
                    $where = "$fieldColumn IS NULL";
                } else {
                    $where = "$fieldColumn = :$colVarName";
                }

                return [
                    'where'  => $where,
                    'params' => [$colVarName => [$checkValue, null]],
                ];

            case Query::OP_NEQ:

                if ($checkValue === null) {
                    $where = "$fieldColumn IS NOT NULL";
                } else {
                    if ($handleNull) {
                        $where = "$fieldColumn != :$colVarName OR $fieldColumn IS NULL";
                    } else {
                        $where = "$fieldColumn != :$colVarName";
                    }
                }

                return [
                    'where'  => $where,
                    'params' => [$colVarName => [$checkValue, null]],
                ];

            case Query::OP_LT:
                return [
                    'where'  => "$fieldColumn < :$colVarName",
                    'params' => [$colVarName => [$checkValue, null]],
                ];

            case Query::OP_LTE:
                return [
                    'where'  => "$fieldColumn <= :$colVarName",
                    'params' => [$colVarName => [$checkValue, null]],
                ];

            case Query::OP_GT:
                return [
                    'where'  => "$fieldColumn > :$colVarName",
                    'params' => [$colVarName => [$checkValue, null]],
                ];

            case Query::OP_GTE:
                return [
                    'where'  => "$fieldColumn >= :$colVarName",
                    'params' => [$colVarName => [$checkValue, null]],
                ];

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

                $op = $operator === Query::OP_NOT_IN ? 'NOT IN' : 'IN';

                $where = "$fieldColumn $op (:$colVarName)";

                if ($operator === Query::OP_NOT_IN && $handleNull) {
                    $where = "$where OR $fieldColumn IS NULL";
                }

                return [
                    'where'  => $where,
                    'params' => [$colVarName => [$checkValue, Connection::PARAM_STR_ARRAY]],
                ];

            case Query::OP_BETWEEN:
            case Query::OP_NOT_BETWEEN:
                if (empty($checkValue[0]) || empty($checkValue[1])) {
                    return false;
                }

                $checkValue = ListUtils::map($checkValue, function ($v) {
                    if ($v instanceof \DateTime) {
                        return $v->format('Y-m-d H:i:s');
                    }

                    return $v;
                });

                $op = $operator === Query::OP_NOT_BETWEEN ? 'NOT BETWEEN' : 'BETWEEN';

                return [
                    'where'  => "$fieldColumn $op :{$colVarName}1 AND :{$colVarName}2",
                    'params' => [
                        "{$colVarName}1" => [$checkValue[0], null],
                        "{$colVarName}2" => [$checkValue[1], null],
                    ],
                ];

            case Query::OP_IS_NULL:
            case Query::OP_NOT_NULL:
                $op = $operator === Query::OP_NOT_NULL ? 'IS NOT NULL' : 'IS NULL';

                return [
                    'where'  => "$fieldColumn $op",
                    'params' => [],
                ];

            case Query::OP_EMPTY:
            case Query::OP_NOT_EMPTY:
                $op = $operator === Query::OP_NOT_EMPTY ? 'IS NOT NULL' : 'IS NULL';

                return [
                    'where'  => "$fieldColumn $op",
                    'params' => [],
                ];

            case Query::OP_EXISTS:
            case Query::OP_NOT_EXISTS:
                $op = $operator === Query::OP_EXISTS ? 'IS NOT NULL' : 'IS NULL';

                return [
                    'where'  => "$fieldColumn $op",
                    'params' => [],
                ];

            default:
                throw new \InvalidArgumentException("Unknown operator: {$operator}");
        }
    }
}
