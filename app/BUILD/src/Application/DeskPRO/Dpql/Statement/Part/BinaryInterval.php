<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Exception;
use Application\DeskPRO\Dpql\Parser;
use Application\DeskPRO\Dpql\Statement\Display;

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
     * @var \Application\DeskPRO\Dpql\Statement\Part\AbstractPart
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
     * @param int                                                   $operator
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart $lhs
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart $rhs
     *
     * @throws \Application\DeskPRO\Dpql\Exception
     */
    public function __construct($operator, AbstractPart $lhs, $amount, $unit)
    {
        if (!isset(self::$_operatorMap[$operator])) {
            throw new Exception("Invalid math operator (token ID: $operator)");
        }

        $this->operator = $operator;
        $this->lhs      = $lhs;
        $this->amount   = $amount;
        $this->unit     = $unit;

        $lowerUnit = strtolower($this->unit);
        if (!isset(self::$_typeMap[$lowerUnit])) {
            throw new Exception("Unknown interval unit $this->unit");
        }
    }

    /**
     * Prepares a part for use, including validating that the usage is valid.
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
        $placeholder = $this->_findPlaceholder();
        if ($placeholder) {
            return $placeholder[0]->prepareWithIntervals(
                $statement, $section, $this->getChildStack($stack), $select, $result, $placeholder[1]
            );
        } else {
            $lhs      = $this->lhs->prepare($statement, $section, $this->getChildStack($stack), $select, $result);
            $operator = self::$_operatorMap[$this->operator];
            $sqlUnit  = self::$_typeMap[strtolower($this->unit)];

            $sql = "({$lhs->sql()} $operator INTERVAL $this->amount $sqlUnit)";

            return new Prepared($sql, "{$lhs->name()} $operator INTERVAL $this->amount $this->unit", false, 'datetime');
        }
    }

    /**
     * Renders a part back to DPQL.
     *
     * @param \Application\DeskPRO\Dpql\Statement\Display             $statement
     * @param string                                                  $section
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[] $stack
     *
     * @return string
     */
    public function toDpql(Display $statement, $section, array $stack)
    {
        return $this->lhs->toDpql($statement, $section, $stack)
            .' '.self::$_operatorMap[$this->operator]." INTERVAL $this->amount $this->unit";
    }

    /**
     * Prepares the interval when it's called in a binary comparison context.
     * The interval is always the right hand side of the comparison.
     *
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart   $lhs        The left hand side of the comparison
     * @param string                                                  $comparison The comparison operator
     * @param \Application\DeskPRO\Dpql\Statement\Display             $statement
     * @param string                                                  $section    Name of the section usage is in (select, where, split, group, order)
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[] $stack      Parent parts
     * @param \Application\DeskPRO\Dpql\SqlSelect                     $select
     * @param \Application\DeskPRO\Dpql\ResultHandler                 $result
     *
     * @throws \Application\DeskPRO\Dpql\Exception
     *
     * @return \Application\DeskPRO\Dpql\Statement\Part\Prepared|bool Prepared results or false the default behavior should be called
     */
    public function prepareComparison(
        AbstractPart $lhs, $comparison, Display $statement, $section, array $stack,
        Dpql\SqlSelect $select, Dpql\ResultHandler $result
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
