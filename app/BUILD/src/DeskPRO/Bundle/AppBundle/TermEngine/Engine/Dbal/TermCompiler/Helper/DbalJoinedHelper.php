<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalJoinedHelper.
 */
class DbalJoinedHelper extends AbstractDbalHelper
{
    /**
     * An identifier for this helper.
     *
     * @return string
     */
    public function getId()
    {
        return 'joined';
    }

    /**
     * @param DbalQueryPart $part
     * @param mixed         $fieldName
     * @param string        $op
     * @param mixed         $input
     *
     * @return DbalQueryPart
     */
    protected function applyWhere(DbalQueryPart $part, $fieldName, $op, $input)
    {
        $fields = $fieldName;
        if (!is_array($fieldName)) {
            $fields = [$fieldName => $input];
        }

        $where  = [];
        $values = [];
        $iter   = 0;
        foreach ($fields as $field => $value) {
            if ($field == '%OP') {
                continue;
            }

            $sql_op = (TermInterface::OP_NOT_HAS === $op) ? '!=' : '=';
            if (is_array($value)) {
                $sql_op = (TermInterface::OP_NOT_HAS === $op) ? 'NOT IN' : 'IN';
            }

            if (is_array($value)) {
                $where[] = sprintf('%s %s (:input%d)', $field, $sql_op, $iter);
            } else {
                $where[] = sprintf('%s %s :input%d', $field, $sql_op, $iter);
            }

            $values[sprintf('input%d', $iter)] = $value;
            ++$iter;
        }

        $flat_operator = ' AND ';
        if (isset($fields['%OP']) && 'OR' == $fields['%OP']) {
            $flat_operator = ' OR ';
        }

        $part->setWhereString(implode($flat_operator, $where));
        $part->setParameters($values);

        return $part;
    }

    /**
     * @param string $fieldName
     * @param string $joinTable
     * @param string $joinClause
     * @param string $op
     * @param bool   $input
     * @param bool   $unique
     *
     * @return DbalQueryPart
     */
    public function buildQueryPart($fieldName, $joinTable, $joinClause, $op, $input = false, $unique = true)
    {
        $part = new DbalQueryPart();

        if ($unique) {
            $part->addUniqueJoin($joinTable, $joinTable, $joinClause);
        } else {
            $part->addJoin($joinTable, $joinClause);
        }

        return $this->applyWhere($part, $fieldName, $op, $input);
    }

    /**
     * @param string $fieldName
     * @param array  $joins
     * @param string $op
     * @param bool   $input
     * @param bool   $unique
     *
     * @return DbalQueryPart
     */
    public function buildMultiJoinQueryPart($fieldName, array $joins, $op, $input = false, $unique = true)
    {
        $part = new DbalQueryPart();

        foreach ($joins as $join_table => $join_clause) {
            if ($unique) {
                $part->addUniqueJoin($join_table, $join_table, $join_clause);
            } else {
                $part->addJoin($join_table, $join_clause);
            }
        }

        return $this->applyWhere($part, $fieldName, $op, $input);
    }
}
