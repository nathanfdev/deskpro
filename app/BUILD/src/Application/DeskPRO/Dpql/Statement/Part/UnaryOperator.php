<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Parser;
use Application\DeskPRO\Dpql\Statement\Display;

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
     * @var \Application\DeskPRO\Dpql\Statement\Part\AbstractPart
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
     * @param int                                                   $operator
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart $value
     */
    public function __construct($operator, AbstractPart $value)
    {
        $this->operator = $operator;
        $this->value    = $value;
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
        $childStack = $this->getChildStack($stack);

        $value        = $this->value->prepare($statement, $section, $childStack, $select, $result);
        $operator     = self::$_operatorMap[$this->operator];
        $operatorType = self::$_operatorTypeMap[$this->operator];

        return new Prepared("($operator{$value->sql()})", "$operator{$value->name()}", false, $operatorType);
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
        return self::$_operatorMap[$this->operator]
            .$this->value->toDpql($statement, $section, $stack);
    }
}
