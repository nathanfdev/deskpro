<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Represents a call to the LIKE operator.
 */
class Like extends AbstractPart
{
    /**
     * @var AbstractPart
     */
    public $lhs;

    /**
     * @var AbstractPart
     */
    public $rhs;

    /**
     * True = LIKE, false = NOT LIKE.
     *
     * @var bool
     */
    public $positive;

    /**
     * Constructor.
     *
     * @param AbstractPart $lhs
     * @param AbstractPart $rhs
     * @param bool         $positive
     */
    public function __construct(AbstractPart $lhs, AbstractPart $rhs, $positive = true)
    {
        $this->lhs      = $lhs;
        $this->rhs      = $rhs;
        $this->positive = $positive;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result)
    {
        $childStack = $this->getChildStack($stack);

        $lhs = $this->lhs->prepare($statement, $section, $childStack, $select, $result);
        $rhs = $this->rhs->prepare($statement, $section, $childStack, $select, $result);
        $not = ($this->positive ? '' : ' NOT');

        $sql = "{$lhs->sql()}$not LIKE {$rhs->sql()}";

        return new Prepared($sql, "{$lhs->name()}$not LIKE {$rhs->name()}", false, 'boolean');
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        $not = ($this->positive ? '' : ' NOT');

        return $this->lhs->toDpql($statement, $section, $stack)
            .$not.' LIKE '
            .$this->rhs->toDpql($statement, $section, $stack);
    }
}
