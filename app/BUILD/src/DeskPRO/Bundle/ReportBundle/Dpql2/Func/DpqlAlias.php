<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * This is used in SELECT/GROUP BY clauses to alias a column. This can be used in other clauses
 * without triggering an error, unlike AS.
 */
class DpqlAlias extends AbstractDpqlFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if (count($arguments) != 2) {
            throw new DpqlException('DPQL_ALIAS() can only accept 2 arguments');
        }

        $childStack = $stack;
        array_shift($childStack); // pop this off the stack - it doesn't exist to the children

        $arg           = reset($arguments);
        $format        = next($arguments);
        $formatLiteral = $this->_toLiteral($format);

        $prepped = $arg->prepare($statement, $section, $childStack, $select, $metadata);
        $prepped->setName($formatLiteral);

        return $prepped;
    }
}
