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

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql2\Statement\Part;

use Application\DeskPRO\Dpql2;
use Application\DeskPRO\Dpql2\Exception;
use Application\DeskPRO\Dpql2\Parser;
use Application\DeskPRO\Dpql2\Statement\SelectPart;

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
     * @var \Application\DeskPRO\Dpql2\Statement\Part\AbstractPart
     */
    public $lhs;

    /**
     * Right hand side of comparison.
     *
     * @var \Application\DeskPRO\Dpql2\Statement\Part\AbstractPart
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
     * @param int                                                    $operator
     * @param \Application\DeskPRO\Dpql2\Statement\Part\AbstractPart $lhs
     * @param \Application\DeskPRO\Dpql2\Statement\Part\AbstractPart $rhs
     *
     * @throws \Application\DeskPRO\Dpql2\Exception
     */
    public function __construct($operator, AbstractPart $lhs, AbstractPart $rhs)
    {
        if (!isset(self::$_operatorMap[$operator])) {
            throw new Exception("Invalid math operator (token ID: $operator)");
        }

        $this->operator = $operator;
        $this->lhs      = $lhs;
        $this->rhs      = $rhs;
    }

    /**
     * Prepares a part for use, including validating that the usage is valid.
     *
     * @param \Application\DeskPRO\Dpql2\Statement\SelectPart          $statement
     * @param string                                                   $section   Name of the section usage is in (select, where, split, group, order)
     * @param \Application\DeskPRO\Dpql2\Statement\Part\AbstractPart[] $stack     Parent parts
     * @param \Application\DeskPRO\Dpql2\SqlSelect                     $select    Select being built up
     * @param \Application\DeskPRO\Dpql2\ResultHandler                 $result
     *
     * @throws \Application\DeskPRO\Dpql2\Exception
     *
     * @return \Application\DeskPRO\Dpql2\Statement\Part\Prepared|bool Prepared results or false if there's no output
     */
    public function prepare(
        SelectPart $statement, $section, array $stack, Dpql2\SqlSelect $select, Dpql2\ResultHandler $result
    ) {
        $childStack = $this->getChildStack($stack);

        $lhs      = $this->lhs->prepare($statement, $section, $childStack, $select, $result);
        $rhs      = $this->rhs->prepare($statement, $section, $childStack, $select, $result);
        $operator = self::$_operatorMap[$this->operator];

        $sql = "({$lhs->sql()} $operator {$rhs->sql()})";

        return new Prepared($sql, "{$lhs->name()} $operator {$rhs->name()}", false, 'number');
    }

    /**
     * Renders a part back to DPQL.
     *
     * @param \Application\DeskPRO\Dpql2\Statement\SelectPart          $statement
     * @param string                                                   $section
     * @param \Application\DeskPRO\Dpql2\Statement\Part\AbstractPart[] $stack
     *
     * @return string
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        return $this->lhs->toDpql($statement, $section, $stack)
            .' '.self::$_operatorMap[$this->operator].' '
            .$this->rhs->toDpql($statement, $section, $stack);
    }
}
