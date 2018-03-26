<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\AbstractPart;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\NullValue;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Helper used in group by to make an X-Y matrix table if 2 valid arguments are supplied.
 */
class DpqlMatrix extends AbstractDpqlFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if ($section != 'group') {
            throw new DpqlException('DPQL_MATRIX() may only be used in GROUP BY.');
        }
        if (count($stack) > 1) {
            // note: the top of the stack is this function
            throw new DpqlException('DPQL_MATRIX() may only be used at the top-level.');
        }

        if (count($arguments) != 2) {
            throw new DpqlException('DPQL_MATRIX() can only accept 2 arguments.');
        }

        $childStack = $stack;
        array_shift($childStack); // pop this off the stack - it doesn't exist to the children

        $valid = [];

        /** @var AbstractPart $arg */
        foreach ($arguments as $arg) {
            if ($arg instanceof NullValue) {
                continue;
            }

            $groupBy = $arg->prepare($statement, $section, $childStack, $select, $metadata);
            if ($groupBy->hasValue()) {
                $valid[] = $groupBy;
            }
        }

        $isMatrix = count($valid) > 1;

        /** @var Prepared $groupBy */
        foreach ($valid as $key => $groupBy) {
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

            if ($key == 0 && $isMatrix) {
                $metadata->addGroupXColumn($groupBy->name(), $groupId, $printId, $groupBy->renderer());
            } else {
                $metadata->addGroupYColumn($groupBy->name(), $groupId, $printId, $groupBy->renderer());
            }
        }

        return new Prepared(false);
    }
}
