<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\QueryCondition;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\Util\ListUtils;
use Doctrine\DBAL\Connection;

abstract class AbstractTermsHandler implements TermsHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCompareFunctions()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function doesTicketMatch($fieldId, $operator, OptValue $options, TicketModel $ticketModel, Context $context, Term $term)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function buildQueryCondition($fieldId, $operator, OptValue $options, Context $context, Term $term)
    {
        return null;
    }

    /**
     * @param mixed          $fieldValue
     * @param string         $operator
     * @param mixed|OptValue $checkValue
     *
     * @return bool
     */
    public function checkValue($fieldValue, $operator, $checkValue)
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
                if (!isset($checkValue[0]) || !isset($checkValue[1])) {
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

    /**
     * @param $fieldColumn
     * @param $operator
     * @param $checkValue
     *
     * @return QueryCondition
     */
    public function checkValueQueryCondition($fieldColumn, $operator, $checkValue, $cond = null)
    {
        $where = $this->checkValueQueryWhere($fieldColumn, $operator, $checkValue);

        if (!$cond) {
            $cond = new QueryCondition();
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
    public function checkValueQueryWhere($fieldColumn, $operator, $checkValue)
    {
        if ($checkValue instanceof OptValue) {
            $checkValue = $checkValue->getValue();
        }

        $colVarName = preg_replace('/[^a-zA-Z0-9]/', '', $fieldColumn);

        switch ($operator) {
            case Query::OP_EQ:
                return [
                    'where'  => "$fieldColumn = :$colVarName",
                    'params' => [$colVarName => [$checkValue, null]],
                ];

            case Query::OP_NEQ:
                return [
                    'where'  => "$fieldColumn != :$colVarName",
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

                return [
                    'where'  => "$fieldColumn $op (:$colVarName)",
                    'params' => [$colVarName => [$checkValue, Connection::PARAM_STR_ARRAY]],
                ];

            case Query::OP_BETWEEN:
            case Query::OP_NOT_BETWEEN:
                if (!isset($checkValue[0]) || !isset($checkValue[1])) {
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
                $op = $operator === Query::OP_NOT_EXISTS ? 'IS NOT NULL' : 'IS NULL';

                return [
                    'where'  => "$fieldColumn $op",
                    'params' => [],
                ];

            default:
                throw new \InvalidArgumentException("Unknown operator: {$operator}");
        }
    }
}
