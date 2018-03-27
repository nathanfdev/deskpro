<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Converts the date within this from the person's time zone to UTC by undoing the adjustment.
 */
class DpqlToUtc extends AbstractDpqlFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if (count($arguments) != 1) {
            throw new DpqlException('DPQL_TO_UTC() can only accept 1 argument');
        }

        $arg = reset($arguments);

        $argPrepared = $arg->prepare($statement, $section, $stack, $select, $metadata);

        $tzOffsetSeconds = $statement->getTimezoneOffsetForFunction($stack);
        $interval        = ($tzOffsetSeconds ? " - INTERVAL $tzOffsetSeconds SECOND" : '');

        return new Prepared("({$argPrepared->sql()}$interval)", "DPQL_TO_UTC({$argPrepared->name()})", false, 'datetime');
    }
}
