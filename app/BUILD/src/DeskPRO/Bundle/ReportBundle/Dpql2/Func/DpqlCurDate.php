<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Gets the current date in YYYY-MM-DD format in the current person's time zone.
 */
class DpqlCurDate extends AbstractDpqlFunc
{
    public static function getName()
    {
        return 'DPQL_CURDATE';
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if (count($arguments)) {
            throw new DpqlException('DPQL_CURDATE() can only accept 0 arguments');
        }

        $tzOffsetSeconds = $statement->getTimezoneOffsetForFunction($stack);
        $interval        = ($tzOffsetSeconds ? " + INTERVAL $tzOffsetSeconds SECOND" : '');

        $sql = "DATE(UTC_TIMESTAMP()$interval)";

        return new Prepared($sql, 'DPQL_CURDATE()', false, 'date');
    }
}
