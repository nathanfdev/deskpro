<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Gets the percentage of all rows in the group that match the given argument.
 * For example, DPQL_PERCENT(table.column > 10).
 */
class DpqlPercent extends AbstractDpqlFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if (!in_array($section, ['select', 'split', 'group', 'order'])) {
            throw new DpqlException('DPQL_PERCENT() may only be used in SELECT, SPLIT BY, GROUP BY, and ORDER BY sections.');
        }

        if (!in_array(count($arguments), [1, 2])) {
            throw new DpqlException('DPQL_PERCENT() can only accept 1 or 2 arguments');
        }

        $condition = reset($arguments);
        $prepped   = $condition->prepare($statement, $section, $stack, $select, $metadata);

        $sql = 'IF(COUNT(*) > 0, (SUM(IF('.$prepped->sql().', 1, 0)) / COUNT(*)) * 100, 0)';

        return new Prepared($sql, 'DPQL_PERCENT('.$prepped->name().')', false, 'percent');
    }
}
