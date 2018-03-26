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
class BinaryMath extends AbstractPart
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
     * Maps from token IDs to printable/usable operators.
     *
     * @var array
     */
    protected static $_operatorMap = [
        Parser::T_OP_PLUS     => '+',
        Parser::T_OP_MINUS    => '-',
        Parser::T_OP_MULTIPLY => '*',
        Parser::T_OP_DIVIDE   => '/',
    ];

    /**
     * Constructor.
     *
     * @param int          $operator
     * @param AbstractPart $lhs
     * @param AbstractPart $rhs
     *
     * @throws \DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException
     */
    public function __construct($operator, AbstractPart $lhs, AbstractPart $rhs)
    {
        if (!isset(self::$_operatorMap[$operator])) {
            throw new DpqlException("Invalid math operator (token ID: $operator)");
        }

        $this->operator = $operator;
        $this->lhs      = $lhs;
        $this->rhs      = $rhs;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result)
    {
        $childStack = $this->getChildStack($stack);

        $lhs      = $this->lhs->prepare($statement, $section, $childStack, $select, $result);
        $rhs      = $this->rhs->prepare($statement, $section, $childStack, $select, $result);
        $operator = self::$_operatorMap[$this->operator];

        $sql = "({$lhs->sql()} $operator {$rhs->sql()})";

        return new Prepared($sql, "{$lhs->name()} $operator {$rhs->name()}", false, 'number');
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
