<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\Parser;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Represents a mathematical operation with 2 elements.
 */
class BinaryInterval extends AbstractPart
{
    /**
     * Token ID of the operator.
     *
     * @var int
     */
    public $operator;

    /**
     * Left hand side of comparison.
     *
     * @var \DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\AbstractPart
     */
    public $lhs;

    /**
     * @var int
     */
    public $amount;

    /**
     * @var string
     */
    public $unit;

    /**
     * Maps from token IDs to printable/usable operators.
     *
     * @var array
     */
    protected static $_operatorMap = [
        Parser::T_OP_PLUS  => '+',
        Parser::T_OP_MINUS => '-',
    ];

    protected static $_typeMap = [
        'seconds' => 'SECOND',
        'second'  => 'SECOND',
        'minutes' => 'MINUTE',
        'minute'  => 'MINUTE',
        'hours'   => 'HOUR',
        'hour'    => 'HOUR',
        'days'    => 'DAY',
        'day'     => 'DAY',
        'weeks'   => 'WEEK',
        'week'    => 'WEEK',
        'months'  => 'MONTH',
        'month'   => 'MONTH',
        'years'   => 'YEAR',
        'year'    => 'YEAR',
    ];

    /**
     * Constructor.
     *
     * @param int          $operator
     * @param AbstractPart $lhs
     * @param int          $amount
     * @param string       $unit
     *
     * @throws \DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException
     */
    public function __construct($operator, AbstractPart $lhs, $amount, $unit)
    {
        if (!isset(self::$_operatorMap[$operator])) {
            throw new DpqlException("Invalid math operator (token ID: $operator)");
        }

        $this->operator = $operator;
        $this->lhs      = $lhs;
        $this->amount   = $amount;
        $this->unit     = $unit;

        $lowerUnit = strtolower($this->unit);
        if (!isset(self::$_typeMap[$lowerUnit])) {
            throw new DpqlException("Unknown interval unit $this->unit");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        $placeholder = $this->_findPlaceholder();
        if ($placeholder) {
            return $placeholder[0]->prepareWithIntervals(
                $statement, $section, $this->getChildStack($stack), $select, $metadata, $placeholder[1]
            );
        } else {
            $lhs      = $this->lhs->prepare($statement, $section, $this->getChildStack($stack), $select, $metadata);
            $operator = self::$_operatorMap[$this->operator];
            $sqlUnit  = self::$_typeMap[strtolower($this->unit)];

            $sql = "({$lhs->sql()} $operator INTERVAL $this->amount $sqlUnit)";

            return new Prepared($sql, "{$lhs->name()} $operator INTERVAL $this->amount $this->unit", false, 'datetime');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        return $this->lhs->toDpql($statement, $section, $stack)
            .' '.self::$_operatorMap[$this->operator]." INTERVAL $this->amount $this->unit";
    }

    /**
     * Prepares the interval when it's called in a binary comparison context.
     * The interval is always the right hand side of the comparison.
     *
     * @param \DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\AbstractPart   $lhs        The left hand side of the comparison
     * @param string                                                           $comparison The comparison operator
     * @param \DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart          $statement
     * @param string                                                           $section    Name of the section usage is in (select, where, split, group, order)
     * @param \DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\AbstractPart[] $stack      Parent parts
     * @param \DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect                     $select
     * @param \DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata              $result
     *
     * @throws \DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException
     *
     * @return \DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared|bool Prepared results or false the default behavior should be called
     */
    public function prepareComparison(
        AbstractPart $lhs, $comparison, SelectPart $statement, $section, array $stack,
        SqlSelect $select, ResultMetadata $result
    ) {
        $placeholder = $this->_findPlaceholder();
        if (!$placeholder) {
            return false;
        }

        return $placeholder[0]->prepareComparison(
            $lhs, $comparison, $statement, $section, $stack, $select, $result, $placeholder[1]
        );
    }

    protected function _findPlaceholder()
    {
        $stack     = $this->lhs;
        $intervals = [$this];

        do {
            if ($stack instanceof Placeholder) {
                return [$stack, $intervals];
            } elseif ($stack instanceof self) {
                $intervals[] = $stack;
                $stack       = $stack->lhs;
            } else {
                return false;
            }
        } while (true);
    }
}
