<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\Parser;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Represents a unary operator (-, NOT, !).
 */
class UnaryOperator extends AbstractPart
{
    /**
     * Token ID of the operator.
     *
     * @var int
     */
    public $operator;

    /**
     * @var AbstractPart
     */
    public $value;

    /**
     * Maps from token IDs to printable/usable operators.
     *
     * @var array
     */
    protected static $_operatorMap = [
        Parser::T_OP_BANG    => '!',
        Parser::T_OP_U_MINUS => '-',
        Parser::T_OP_MINUS   => '-',
        Parser::T_OP_NOT     => 'NOT ', // space after is important
    ];

    /**
     * @var array
     */
    protected static $_operatorTypeMap = [
        Parser::T_OP_BANG    => 'boolean',
        Parser::T_OP_U_MINUS => 'number',
        Parser::T_OP_MINUS   => 'number',
        Parser::T_OP_NOT     => 'boolean',
    ];

    /**
     * Constructor.
     *
     * @param int          $operator
     * @param AbstractPart $value
     */
    public function __construct($operator, AbstractPart $value)
    {
        $this->operator = $operator;
        $this->value    = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(
        SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result
    ) {
        $childStack = $this->getChildStack($stack);

        $value        = $this->value->prepare($statement, $section, $childStack, $select, $result);
        $operator     = self::$_operatorMap[$this->operator];
        $operatorType = self::$_operatorTypeMap[$this->operator];

        return new Prepared("($operator{$value->sql()})", "$operator{$value->name()}", false, $operatorType);
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        return self::$_operatorMap[$this->operator]
            .$this->value->toDpql($statement, $section, $stack);
    }
}
