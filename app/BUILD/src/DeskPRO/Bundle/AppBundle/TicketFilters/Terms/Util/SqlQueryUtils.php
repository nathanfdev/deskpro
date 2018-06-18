<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util;

use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlCondition;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use Doctrine\DBAL\Connection;

/**
 * Class SqlQueryUtils.
 */
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
     * @param bool           $handleNull
     *
     * @throws \InvalidArgumentException
     *
     * @return array Array of ['where' => XXX, 'params' => ['x' => ['val', type]]]
     */
    public static function buildQueryWhere($fieldColumn, $operator, $checkValue, $handleNull = false)
    {
        $checkValue = ValueFormatter::formatCheckValue($checkValue);
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
                $checkValue = ValueFormatter::formatList($checkValue);
                $op         = $operator === Query::OP_NOT_IN ? 'NOT IN' : 'IN';

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
                $checkValue = ValueFormatter::formatRange($checkValue);
                if (!$checkValue) {
                    return false;
                }

                $op = $operator === Query::OP_NOT_BETWEEN ? 'NOT BETWEEN' : 'BETWEEN';

                return [
                    'where'  => "$fieldColumn $op :{$colVarName}1 AND :{$colVarName}2",
                    'params' => [
                        "{$colVarName}1" => [$checkValue[0], null],
                        "{$colVarName}2" => [$checkValue[1], null],
                    ],
                ];

            case Query::OP_IS_NULL:
            case Query::OP_EMPTY:
            case Query::OP_NOT_EXISTS:
                return [
                    'where'  => "$fieldColumn IS NULL",
                    'params' => [],
                ];

            case Query::OP_NOT_NULL:
            case Query::OP_NOT_EMPTY:
            case Query::OP_EXISTS:
                return [
                    'where'  => "$fieldColumn IS NOT NULL",
                    'params' => [],
                ];

            default:
                throw new \InvalidArgumentException("Unknown operator: {$operator}");
        }
    }
}
