<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Handler that wraps around COUNT(DISTINCT x).
 */
class DpqlCountDistinct extends AbstractDpqlFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if (!in_array($section, ['select', 'split', 'group', 'order'])) {
            throw new DpqlException('DPQL_COUNT_DISTINCT() may only be used in SELECT, SPLIT BY, GROUP BY, and ORDER BY sections.');
        }

        if (count($arguments) != 1) {
            throw new DpqlException('DPQL_COUNT_DISTINCT() can only accept 1 argument.');
        }

        $expression = reset($arguments);
        $prepped    = $expression->prepare($statement, $section, $stack, $select, $metadata);

        $sql = 'COUNT(DISTINCT '.$prepped->sql().')';
        $res = new Prepared($sql, 'DPQL_COUNT_DISTINCT('.$prepped->name().')', false, 'number');

        return $res;
    }
}
