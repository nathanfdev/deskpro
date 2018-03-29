<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\AbstractPart;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * This is used in SPLIT/GROUP BY clauses to ensure that the printed value
 * can be different than the split value. For example, DPQL_PRINT(tickets.subject, tickets.id).
 */
class DpqlPrintable extends AbstractDpqlFunc
{
    /**
     * {@inheritdoc}
     */
    public static function getName()
    {
        return 'DPQL_PRINT';
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if (!in_array($section, ['split', 'group'])) {
            throw new DpqlException('DPQL_PRINT() may only be used in SPLIT BY and GROUP BY sections.');
        }

        if (count($arguments) != 2) {
            throw new DpqlException('DPQL_PRINT() can only accept 2 arguments');
        }

        $childStack = $stack;
        array_shift($childStack); // pop this off the stack - it doesn't exist to the children

        /** @var AbstractPart $sql */
        /** @var AbstractPart $print */
        $sql   = reset($arguments);
        $print = next($arguments);

        $printPrepped = $print->prepare($statement, $section, $childStack, $select, $metadata);
        $sqlPrepped   = $sql->prepare($statement, $section, $childStack, $select, $metadata);

        return new Prepared(
            $sqlPrepped->sql(), $printPrepped->name(), $printPrepped->printed(), $printPrepped->renderer()
        );
    }
}
