<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Handler for DPQL_TOTAL function.
 */
class DpqlTotal extends AbstractDpqlFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if ($section != 'select') {
            throw new DpqlException('DPQL_TOTAL() may only be used in SELECT.');
        }
        if (count($arguments) != 1) {
            throw new DpqlException('DPQL_TOTAL() can only accept 1 argument.');
        }
        if (count($stack) > 1) {
            // note: the top of the stack is this function
            throw new DpqlException('DPQL_TOTAL() may only be used at the top-level.');
        }

        $childStack = $stack;
        array_shift($childStack); // pop this off the stack - it doesn't exist to the children

        $expression = reset($arguments);
        $prepped    = $expression->prepare($statement, $section, $stack, $select, $metadata);
        $prepped->setTotal(true);

        return $prepped;
    }
}
