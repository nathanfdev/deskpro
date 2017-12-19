<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\AppBundle\Dpql2\Exception;
use DeskPRO\Bundle\AppBundle\Dpql2\Parser;
use DeskPRO\Bundle\AppBundle\Dpql2\ResultHandler;
use DeskPRO\Bundle\AppBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\AppBundle\Dpql2\Statement\SelectPart;

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
     * @var \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart
     */
    public $lhs;

    /**
     * Right hand side of comparison.
     *
     * @var \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart
     */
    public $rhs;

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
     *
     * @throws \DeskPRO\Bundle\AppBundle\Dpql2\Exception
     */
    public function __construct($operator, AbstractPart $lhs, AbstractPart $rhs)
    {
        if (!isset(self::$_operatorMap[$operator])) {
            throw new Exception("Invalid comparison operator (token ID: $operator)");
        }

        $this->operator = $operator;
        $this->lhs      = $lhs;
        $this->rhs      = $rhs;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultHandler $result)
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
            $prepared = $rhs->prepareComparison(
                $lhs, $operator, $statement, $section, $childStack, $select, $result
            );
            if ($prepared) {
                return $prepared;
            }
        }

        $lhsRes = $lhs->prepare($statement, $section, $childStack, $select, $result);
        $rhsRes = $rhs->prepare($statement, $section, $childStack, $select, $result);

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
