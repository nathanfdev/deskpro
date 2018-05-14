<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Represents a call to the REGEXP operator.
 */
class RegExp extends AbstractPart
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
     * True = REGEXP, false = NOT REGEXP.
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
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        $childStack = $this->getChildStack($stack);

        $lhs = $this->lhs->prepare($statement, $section, $childStack, $select, $metadata);
        $rhs = $this->rhs->prepare($statement, $section, $childStack, $select, $metadata);
        $not = ($this->positive ? '' : ' NOT');

        $sql = "{$lhs->sql()}$not REGEXP {$rhs->sql()}";

        return new Prepared($sql, "{$lhs->name()}$not REGEXP {$rhs->name()}", false, 'boolean');
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        $not = ($this->positive ? '' : ' NOT');

        return $this->lhs->toDpql($statement, $section, $stack)
            .$not.' REGEXP '
            .$this->rhs->toDpql($statement, $section, $stack);
    }
}
