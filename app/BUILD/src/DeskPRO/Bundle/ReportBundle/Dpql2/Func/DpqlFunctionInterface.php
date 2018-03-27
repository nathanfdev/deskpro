<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\AbstractPart;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Interface DpqlFunctionInterface.
 */
interface DpqlFunctionInterface
{
    /**
     * @return string
     */
    public static function getName();

    /**
     * Prepares the function for use, including validating that the usage is valid.
     *
     * @param array          $arguments
     * @param SelectPart     $statement
     * @param string         $section   Name of the section usage is in (select, where, split, group, order)
     * @param AbstractPart[] $stack     Parent parts
     * @param SqlSelect      $select    Select being built up
     * @param ResultMetadata $metadata
     *
     * @throws DpqlException
     *
     * @return Prepared|bool Prepared results or false if there's no output
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata);
}
