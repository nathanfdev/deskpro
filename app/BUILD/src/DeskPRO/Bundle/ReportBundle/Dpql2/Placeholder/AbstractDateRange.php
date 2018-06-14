<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\Parser;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\AbstractPart;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\BinaryInterval;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Abstract base for a date range placeholder (such as %TODAY% or %THIS_YEAR%).
 */
abstract class AbstractDateRange extends AbstractPlaceholder
{
    /**
     * Gets the date range that this covers. It must have 3 parts:
     *  - 0: printable version of range
     *  - 1: start of range
     *  - 2: end of range.
     *
     * @return string[int]
     */
    abstract public function getDateRange();

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
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result)
    {
        $range = $this->getDateRange();

        return new Prepared($select->quoteForSql($range[0]));
    }

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
    public function prepareWithIntervals(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result, array $intervals = [])
    {
        $range = $this->getDateRange();

        return new Prepared($select->quoteForSql($this->adjustForIntervals($range[0], $intervals)));
    }

    /**
     * Prepares the placeholder when it's called in a binary comparison context.
     * The placeholder is always the right hand side of the comparison.
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
    ) {
        $lhsRes = $lhs->prepare($statement, $section, $stack, $select, $result);

        $lhsSql     = $lhsRes->sql();
        $lhsName    = $lhsRes->name();
        $dpql       = $this->_toDpql();
        $outputName = "$lhsName $comparison $dpql";

        $range = $this->getDateRange();
        if (!isset($range[1]) && !isset($range[2])) {
            return new Prepared('1', $outputName);
        }

        $rangeStart       = $this->adjustForIntervals($range[1], $intervals);
        $rangeEnd         = $this->adjustForIntervals($range[2], $intervals);
        $beforeRangeStart = null;
        if (isset($range[3])) {
            $matches = [];
            preg_match_all('#\((.*) (\+|-) INTERVAL (\d+) SECOND\)#', $lhsSql, $matches);
            $hackIntervals = [];
            if (@$matches[2][0] && @$matches[3][0]) {
                $hackIntervals[] = new BinaryInterval(
                    '+' === $matches[2][0] ? Parser::T_OP_MINUS : Parser::T_OP_PLUS,
                    $lhs,
                    (int) $matches[3][0],
                    'seconds'
                );
            }
            $beforeRangeStart = $this->adjustForIntervals($range[3], $hackIntervals);
        }

        switch ($comparison) {
            case '=':
                $sql = "$lhsSql BETWEEN '$rangeStart' AND '$rangeEnd'";
                if ($beforeRangeStart) {
                    $newLhsSql = preg_replace('#\((.*) (\+|-) INTERVAL \d+ SECOND\)#', '$1', $lhsSql);

                    $sql = "($newLhsSql > '$beforeRangeStart' AND ".$sql.") OR $newLhsSql > NOW()";
                }
                break;

            case '<>':
                $sql = "$lhsSql NOT BETWEEN '$rangeStart' AND '$rangeEnd'";
                break;

            case '>':
                $sql = "$lhsSql > '$rangeEnd'";
                break;

            case '>=':
                $sql = "$lhsSql >= '$rangeStart'";
                break;

            case '<':
                $sql = "$lhsSql < '$rangeStart'";
                break;

            case '<=':
                $sql = "$lhsSql <= '$rangeEnd'";
                break;
        }

        return new Prepared("($sql)", $outputName);
    }

    /**
     * @param string $date
     * @param array  $intervals
     *
     * @throws DpqlException
     *
     * @return string
     */
    protected function adjustForIntervals($date, array $intervals)
    {
        if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $date)) {
            $format = 'Y-m-d';
        } elseif (preg_match('/^\d{1,2}:\d{1,2}:\d{1,2}$/', $date)) {
            $format = 'H:i:s';
        } else {
            $format = 'Y-m-d H:i:s';
        }
        try {
            $dt = new \DateTime($date);
        } catch (\Exception $e) {
            throw new DpqlException($e->getMessage(), $e->getCode());
        }

        foreach ($intervals as $interval) {
            $operator = $interval->operator == Parser::T_OP_PLUS ? '+' : '-';
            $dt->modify("$operator $interval->amount $interval->unit");
        }

        return $dt->format($format);
    }

    /**
     * @return \DateTime
     */
    protected function getDate()
    {
        $context = $this->dpqlContextStorage->getContext();
        if (!$context) {
            return;
        }

        return $context->getDate();
    }
}
