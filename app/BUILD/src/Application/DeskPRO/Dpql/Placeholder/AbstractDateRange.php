<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Placeholder;

use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql\Statement\Part\AbstractPart;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;

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
    abstract protected function _getDateRange();

    /**
     * Prepares the placeholder for use, including validating that the usage is valid.
     *
     * @param \Application\DeskPRO\Dpql\Statement\Display             $statement
     * @param string                                                  $section   Name of the section usage is in (select, where, split, group, order)
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[] $stack     Parent parts
     * @param \Application\DeskPRO\Dpql\SqlSelect                     $select    Select being built up
     * @param \Application\DeskPRO\Dpql\ResultHandler                 $result
     *
     * @throws \Application\DeskPRO\Dpql\Exception
     *
     * @return \Application\DeskPRO\Dpql\Statement\Part\Prepared|bool Prepared results or false if there's no output
     */
    public function prepare(
        Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
    ) {
        $range = $this->_getDateRange();

        return new Prepared($select->quoteForSql($range[0]));
    }

    /**
     * Prepares the placeholder for use, including validating that the usage is valid.
     *
     * @param \Application\DeskPRO\Dpql\Statement\Display               $statement
     * @param string                                                    $section   Name of the section usage is in (select, where, split, group, order)
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[]   $stack     Parent parts
     * @param \Application\DeskPRO\Dpql\SqlSelect                       $select    Select being built up
     * @param \Application\DeskPRO\Dpql\ResultHandler                   $result
     * @param \Application\DeskPRO\Dpql\Statement\Part\BinaryInterval[] $intervals List of intervals that affect this calculation
     *
     * @throws \Application\DeskPRO\Dpql\Exception
     *
     * @return \Application\DeskPRO\Dpql\Statement\Part\Prepared|bool Prepared results or false if there's no output
     */
    public function prepareWithIntervals(
        Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result, array $intervals = []
    ) {
        $range = $this->_getDateRange();

        return new Prepared($select->quoteForSql($this->_adjustForIntervals($range[0], $intervals)));
    }

    /**
     * Prepares the placeholder when it's called in a binary comparison context.
     * The placeholder is always the right hand side of the comparison.
     *
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart     $lhs        The left hand side of the comparison
     * @param string                                                    $comparison The comparison operator
     * @param \Application\DeskPRO\Dpql\Statement\Display               $statement
     * @param string                                                    $section    Name of the section usage is in (select, where, split, group, order)
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[]   $stack      Parent parts
     * @param \Application\DeskPRO\Dpql\SqlSelect                       $select
     * @param \Application\DeskPRO\Dpql\ResultHandler                   $result
     * @param \Application\DeskPRO\Dpql\Statement\Part\BinaryInterval[] $intervals  List of intervals that affect this calculation
     *
     * @throws \Application\DeskPRO\Dpql\Exception
     *
     * @return \Application\DeskPRO\Dpql\Statement\Part\Prepared|bool Prepared results or false the default behavior should be called
     */
    public function prepareComparison(
        AbstractPart $lhs, $comparison, Display $statement, $section, array $stack,
        Dpql\SqlSelect $select, Dpql\ResultHandler $result, array $intervals = []
    ) {
        $lhsRes = $lhs->prepare($statement, $section, $stack, $select, $result);

        $lhsSql     = $lhsRes->sql();
        $lhsName    = $lhsRes->name();
        $dpql       = $this->_toDpql();
        $outputName = "$lhsName $comparison $dpql";

        $range = $this->_getDateRange();
        if (!isset($range[1]) && !isset($range[2])) {
            return new Prepared('1', $outputName);
        }

        $rangeStart = $this->_adjustForIntervals($range[1], $intervals);
        $rangeEnd   = $this->_adjustForIntervals($range[2], $intervals);

        switch ($comparison) {
            case '=':
                $sql = "$lhsSql BETWEEN '$rangeStart' AND '$rangeEnd'";
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

    protected function _adjustForIntervals($date, array $intervals)
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
            throw new Dpql\Exception($e->getMessage(), $e->getCode());
        }

        foreach ($intervals as $interval) {
            $operator = $interval->operator == \Application\DeskPRO\Dpql\Parser::T_OP_PLUS ? '+' : '-';
            $dt->modify("$operator $interval->amount $interval->unit");
        }

        return $dt->format($format);
    }
}
