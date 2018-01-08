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
