<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\AbstractPart;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\BinaryInterval;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Interface DpqlPlaceholderInterface.
 */
interface DpqlPlaceholderInterface
{
    /**
     * @return string
     */
    public static function getName();

    /**
     * Prepares the placeholder for use, including validating that the usage is valid.
     *
     * @param SqlSelect      $statement
     * @param string         $section   Name of the section usage is in (select, where, split, group, order)
     * @param AbstractPart[] $stack     Parent parts
     * @param SqlSelect      $select    Select being built up
     * @param ResultMetadata $result
     *
     * @throws DpqlException
     *
     * @return Prepared|bool Prepared results or false if there's no output
     */
    public function prepare(SqlSelect $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result);

    /**
     * Prepares the placeholder for use, including validating that the usage is valid.
     *
     * @param SqlSelect        $statement
     * @param string           $section   Name of the section usage is in (select, where, split, group, order)
     * @param AbstractPart[]   $stack     Parent parts
     * @param SqlSelect        $select    Select being built up
     * @param ResultMetadata   $result
     * @param BinaryInterval[] $intervals List of intervals that affect this calculation
     *
     * @throws DpqlException
     *
     * @return Prepared|bool Prepared results or false if there's no output
     */
    public function prepareWithIntervals(SqlSelect $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result, array $intervals = []);

    /**
     * Prepares the placeholder when it's called in a binary comparison context.
     * The placeholder is always the right hand side of the comparison. This is
     * needed for placeholders that actually map to date ranges rather than scalars.
     *
     * @param AbstractPart     $lhs        The left hand side of the comparison
     * @param string           $comparison The comparison operator
     * @param SelectPart       $statement
     * @param string           $section    Name of the section usage is in (select, where, split, group, order)
     * @param AbstractPart[]   $stack      Parent parts
     * @param SqlSelect        $select
     * @param ResultMetadata   $result
     * @param BinaryInterval[] $intervals  List of intervals that affect this calculation
     *
     * @throws DpqlException
     *
     * @return Prepared|bool Prepared results or false the default behavior should be called
     */
    public function prepareComparison(
        AbstractPart $lhs, $comparison, SelectPart $statement, $section, array $stack,
        SqlSelect $select, ResultMetadata $result, array $intervals = []
    );
}
