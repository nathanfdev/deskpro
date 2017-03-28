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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

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

    protected function applyWhere(DbalQueryPart $part, $field_name, $op, $input)
    {
        $fields = $field_name;
        if (!is_array($field_name)) {
            $fields = [$field_name => $input];
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
     * @param mixed $field_name
     * @param $op
     * @param array $num
     * @param null  $num2
     *
     * @return DbalQueryPart
     */
    public function buildQueryPart($field_name, $join_table, $join_clause, $op, $input = false, $unique = true)
    {
        $part = new DbalQueryPart();

        if ($unique) {
            $part->addUniqueJoin($join_table, $join_table, $join_clause);
        } else {
            $part->addJoin($join_table, $join_clause);
        }

        return $this->applyWhere($part, $field_name, $op, $input);
    }

    /**
     * @param mixed $field_name
     * @param $op
     * @param array $num
     * @param null  $num2
     *
     * @return DbalQueryPart
     */
    public function buildMultiJoinQueryPart($field_name, $joins, $op, $input = false, $unique = true)
    {
        $part = new DbalQueryPart();

        foreach ($joins as $join_table => $join_clause) {
            if ($unique) {
                $part->addUniqueJoin($join_table, $join_table, $join_clause);
            } else {
                $part->addJoin($join_table, $join_clause);
            }
        }

        return $this->applyWhere($part, $field_name, $op, $input);
    }

    /**
     * Join multiple query parts.
     *
     * @param array $joins       is an array of tablename => clause for the main join
     * @param array $query_parts is an array of DbalQueryPart which will be merged into the join
     *
     * @return DbalQueryPart
     */
    public function buildJoinedQueryParts(array $joins, array $query_parts)
    {
        $part = new DbalQueryPart();

        foreach ($joins as $join_table => $join_clause) {
            if ($unique) {
                $part->addUniqueJoin($join_table, $join_table, $join_clause);
            } else {
                $part->addJoin($join_table, $join_clause);
            }
        }

        $where  = [];
        $values = [];
        foreach ($query_parts as $query_part) {
            $where[] = $query_part->getWhereString();
            $values  = array_merge($values, $query_part->getParameters());
        }

        $part->setWhereString(implode('AND', $where));
        $part->setParameters($values);

        return $part;
    }
}
