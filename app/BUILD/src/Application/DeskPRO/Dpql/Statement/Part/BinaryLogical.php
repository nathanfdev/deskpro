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
 * Represents a logical comparison (AND, OR) with 2 elements.
 */
class BinaryLogical extends AbstractPart
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
     * Right hand side of comparison.
     *
     * @var \Application\DeskPRO\Dpql\Statement\Part\AbstractPart
     */
    public $rhs;

    /**
     * Maps from token IDs to printable/usable operators.
     *
     * @var array
     */
    protected static $_operatorMap = [
        Parser::T_OP_AND => 'AND',
        Parser::T_OP_OR  => 'OR',
    ];

    /**
     * @param int                                                   $operator
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart $lhs
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart $rhs
     *
     * @throws \Application\DeskPRO\Dpql\Exception
     */
    public function __construct($operator, AbstractPart $lhs, AbstractPart $rhs)
    {
        if (!isset(self::$_operatorMap[$operator])) {
            throw new Exception("Invalid logical operator (token ID: $operator)");
        }

        $this->operator = $operator;
        $this->lhs      = $lhs;
        $this->rhs      = $rhs;
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

        $lhs      = $this->lhs->prepare($statement, $section, $childStack, $select, $result);
        $rhs      = $this->rhs->prepare($statement, $section, $childStack, $select, $result);
        $operator = self::$_operatorMap[$this->operator];

        $sql = "({$lhs->sql()} $operator {$rhs->sql()})";

        return new Prepared($sql, "{$lhs->name()} $operator {$rhs->name()}", false, 'boolean');
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
            .' '.self::$_operatorMap[$this->operator].' '
            .$this->rhs->toDpql($statement, $section, $stack);
    }
}
