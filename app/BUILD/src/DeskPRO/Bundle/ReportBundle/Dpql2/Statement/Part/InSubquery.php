<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Represents a call to the IN/NOT IN operator.
 */
class InSubquery extends AbstractPart
{
    /**
     * @var AbstractPart
     */
    public $lhs;

    /**
     * @var SelectPart
     */
    public $subselect;

    /**
     * True = IN, false = NOT IN.
     *
     * @var bool
     */
    public $positive;

    /**
     * Constructor.
     *
     * @param AbstractPart $lhs
     * @param SelectPart   $subselect
     * @param bool         $positive
     */
    public function __construct(AbstractPart $lhs, SelectPart $subselect, $positive = true)
    {
        $this->lhs       = $lhs;
        $this->subselect = $subselect;
        $this->positive  = $positive;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        $childStack = $this->getChildStack($stack);

        $lhs = $this->lhs->prepare($statement, $section, $childStack, $select, $metadata);
        $not = ($this->positive ? '' : ' NOT');

        $this->subselect->prepare();

        $sql = "{$lhs->sql()}$not IN (".$this->subselect->toSql().')';

        return new Prepared($sql, "{$lhs->name()}$not IN subquery", false, 'boolean');
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        $not = ($this->positive ? '' : ' NOT');

        return $this->lhs->toDpql($statement, $section, $stack).$not.' IN ('.$this->subselect->toDpql().')';
    }
}
