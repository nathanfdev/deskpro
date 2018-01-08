<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlDate;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\Parser;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\AbstractPart;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\BinaryInterval;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Abstract base for a date range placeholder (such as %TODAY% or %THIS_YEAR%).
 */
abstract class AbstractDateRange extends AbstractPlaceholder
{
    /**
     * @var \DateTime
     */
    protected $dpqlDate;

    /**
     * Constructor.
     *
     * @param TokenStorage $tokenStorage
     * @param DpqlDate     $dpqlDate
     */
    public function __construct(TokenStorage $tokenStorage, DpqlDate $dpqlDate)
    {
        parent::__construct($tokenStorage);
        $this->dpqlDate = $dpqlDate;
    }

    /**
     * Gets the date range that this covers. It must have 3 parts:
     *  - 0: printable version of range
     *  - 1: start of range
     *  - 2: end of range.
     *
     * @return string[int]
     */
    abstract protected function getDateRange();

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
    public function prepare(SqlSelect $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result)
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
    public function prepareWithIntervals(SqlSelect $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result, array $intervals = [])
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

        $rangeStart = $this->adjustForIntervals($range[1], $intervals);
        $rangeEnd   = $this->adjustForIntervals($range[2], $intervals);

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
}
