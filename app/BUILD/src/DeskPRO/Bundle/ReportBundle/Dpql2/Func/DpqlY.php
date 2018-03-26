<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\NullValue;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Used in the GROUP BY clause to specify columns that will be grouped in the Y direction in a matrix table.
 */
class DpqlY extends AbstractDpqlFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if ($section != 'group') {
            throw new DpqlException('DPQL_Y() may only be used in GROUP BY.');
        }
        if (count($stack) > 1) {
            // note: the top of the stack is this function
            throw new DpqlException('DPQL_Y() may only be used at the top-level.');
        }

        $childStack = $stack;
        array_shift($childStack); // pop this off the stack - it doesn't exist to the children

        foreach ($arguments as $arg) {
            if ($arg instanceof NullValue) {
                continue;
            }

            $groupBy = $arg->prepare($statement, $section, $childStack, $select, $metadata);
            if ($groupBy->hasValue()) {
                $printId = $select->addSelectField($groupBy->printed());
                $select->addGroupBy($groupBy->sql());
                $defaultOrder = $statement->addDefaultOrder($groupBy->printed());

                if ($groupBy->printed() === $groupBy->sql()) {
                    $groupId = $printId;
                } else {
                    $groupId = $select->addSelectField($groupBy->sql());
                }

                if ($groupBy->ordered() == $groupBy->printed()) {
                    $orderId = $printId;
                } else {
                    $orderId = $select->addSelectField($groupBy->ordered());
                }

                if ($defaultOrder && $groupBy->groupFill()) {
                    $statement->addGroupFill($groupBy->groupFill(), $printId, $groupId, $orderId);
                }

                $metadata->addGroupYColumn($groupBy->name(), $groupId, $printId, $groupBy->renderer());
            }
        }

        return new Prepared(false);
    }
}
