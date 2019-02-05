<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlContext;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\Parser;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Represents a binary comparison (=, >, <=, etc).
 */
class BinaryComparison extends AbstractPart
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
     * Right hand side of comparison.
     *
     * @var \DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\AbstractPart
     */
    public $rhs;

    /**
     * @var DpqlContext context contains a user, so we can add user TZ for placeholder
     */
    private $context;

    /**
     * Maps from token IDs to printable/usable operators.
     *
     * @var array
     */
    protected static $_operatorMap = [
        Parser::T_OP_EQ   => '=',
        Parser::T_OP_NE   => '<>',
        Parser::T_OP_GT   => '>',
        Parser::T_OP_GTEQ => '>=',
        Parser::T_OP_LT   => '<',
        Parser::T_OP_LTEQ => '<=',
    ];

    /**
     * This is used when a comparison needs to be flipped (for placeholders, for example).
     * Maps from the original operator string to the equivalent when the
     * comparison's LHS and RHS are swapped.
     *
     * @var array
     */
    protected static $_operatorOrderFlipped = [
        '='  => '=',
        '<>' => '<>',
        '>'  => '<',
        '>=' => '<=',
        '<'  => '>',
        '<=' => '>=',
    ];

    /**
     * Constructor.
     *
     * @param int          $operator
     * @param AbstractPart $lhs
     * @param AbstractPart $rhs
     * @param DpqlContext  $context
     *
     * @throws \DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException
     */
    public function __construct($operator, AbstractPart $lhs, AbstractPart $rhs, DpqlContext $context)
    {
        if (!isset(self::$_operatorMap[$operator])) {
            throw new DpqlException("Invalid comparison operator (token ID: $operator)");
        }

        $this->operator = $operator;
        $this->lhs      = $lhs;
        $this->rhs      = $rhs;
        $this->context  = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        $childStack = $this->getChildStack($stack);

        $lhs      = $this->lhs;
        $rhs      = $this->rhs;
        $operator = self::$_operatorMap[$this->operator];

        if ($lhs instanceof Placeholder || $lhs instanceof BinaryInterval) {
            // flip as placeholder/interval comparison expects placeholder/interval on RHS
            $temp     = $lhs;
            $lhs      = $rhs;
            $rhs      = $temp;
            $operator = self::$_operatorOrderFlipped[$operator];
        }

        if ($rhs instanceof Placeholder || $rhs instanceof BinaryInterval) {
            $intervals = [];
            if ($rhs instanceof Placeholder) {
                $tzOffset = $this->context->getTimezoneOffsetSeconds();
                if ($tzOffset) {
                    $intervalsOperator = $tzOffset > 0 ? Parser::T_OP_MINUS : $intervalsOperator = Parser::T_OP_PLUS;
                    $intervals[]       = new BinaryInterval($intervalsOperator, $lhs, abs($tzOffset), 'seconds');
                }
            }

            $prepared = $rhs->prepareComparison(
                $lhs, $operator, $statement, $section, $childStack, $select, $metadata, $intervals
            );
            if ($prepared) {
                return $prepared;
            }
        }

        $lhsRes = $lhs->prepare($statement, $section, $childStack, $select, $metadata);
        $rhsRes = $rhs->prepare($statement, $section, $childStack, $select, $metadata);

        $title = "{$lhsRes->name()} $operator {$rhsRes->name()}";

        if ($rhs instanceof NullValue) {
            if ($this->operator == Parser::T_OP_EQ) {
                return new Prepared("({$lhsRes->sql()} IS NULL)", $title);
            } elseif ($this->operator == Parser::T_OP_NE) {
                return new Prepared("({$lhsRes->sql()} IS NOT NULL)", $title);
            }
        }

        return new Prepared("({$lhsRes->sql()} $operator {$rhsRes->sql()})", $title, false, 'boolean');
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        return $this->lhs->toDpql($statement, $section, $stack)
            .' '.self::$_operatorMap[$this->operator].' '
            .$this->rhs->toDpql($statement, $section, $stack);
    }
}
