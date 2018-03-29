<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Interface DpqlStatementPartInterface.
 */
interface DpqlStatementPartInterface
{
    /**
     * Prepares a part for use, including validating that the usage is valid.
     *
     * @param SelectPart     $statement
     * @param string         $section   Name of the section usage is in (select, where, split, group, order)
     * @param AbstractPart[] $stack     Parent parts
     * @param SqlSelect      $select    Select being built up
     * @param ResultMetadata $result
     *
     * @throws \DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException
     *
     * @return Prepared|bool Prepared results or false if there's no output
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result);

    /**
     * Renders a part back to DPQL.
     *
     * @param SelectPart     $statement
     * @param string         $section
     * @param AbstractPart[] $stack
     *
     * @return string
     */
    public function toDpql(SelectPart $statement, $section, array $stack);
}
