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

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\Parser;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

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
        Parser::T_OP_AND => 'AND',
        Parser::T_OP_OR  => 'OR',
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
            throw new DpqlException("Invalid logical operator (token ID: $operator)");
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

        return new Prepared($sql, "{$lhs->name()} $operator {$rhs->name()}", false, 'boolean');
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
