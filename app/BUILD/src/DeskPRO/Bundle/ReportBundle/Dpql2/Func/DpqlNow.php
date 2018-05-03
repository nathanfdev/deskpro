<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Gets the current datetime (YYYY-MM-DD HH:MM:SS) in the current person's time zone.
 */
class DpqlNow extends AbstractDpqlFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if (count($arguments)) {
            throw new DpqlException('DPQL_NOW() can only accept 0 arguments');
        }

        $tzOffsetSeconds = $statement->getTimezoneOffsetForFunction($stack);
        $interval        = ($tzOffsetSeconds ? " + INTERVAL $tzOffsetSeconds SECOND" : '');

        $sql = "(UTC_TIMESTAMP()$interval)";

        return new Prepared($sql, 'DPQL_NOW()', false, 'datetime');
    }
}
