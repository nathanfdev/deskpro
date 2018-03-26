<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Handler for DPQL_COUNT() DPQL function calls, which can work like COUNT(*) with
 * no arguments, but can also take an argument and only count those rows that
 * match the argument.
 */
class DpqlCount extends AbstractDpqlFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if (!in_array($section, ['select', 'split', 'group', 'order'])) {
            throw new DpqlException('DPQL_COUNT() may only be used in SELECT, SPLIT BY, GROUP BY, and ORDER BY sections.');
        }

        if (!$arguments) {
            $res = new Prepared('COUNT(*)', 'DPQL_COUNT()', false, 'number');
        } else {
            if (count($arguments) > 1) {
                throw new DpqlException('DPQL_COUNT() can only accept 0 or 1 argument');
            }

            $condition = reset($arguments);
            $prepped   = $condition->prepare($statement, $section, $stack, $select, $metadata);

            $sql = 'SUM(IF('.$prepped->sql().', 1, 0))';
            $res = new Prepared($sql, 'DPQL_COUNT('.$prepped->name().')', false, 'number');
        }

        return $res;
    }
}
