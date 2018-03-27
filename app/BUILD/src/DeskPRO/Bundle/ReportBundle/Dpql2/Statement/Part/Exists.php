<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Class Exists.
 */
class Exists extends AbstractPart
{
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
     * @param SelectPart $subselect
     * @param bool       $positive
     */
    public function __construct(SelectPart $subselect, $positive = true)
    {
        $this->subselect = $subselect;
        $this->positive  = $positive;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result)
    {
        $this->subselect->prepare();

        $not = ($this->positive ? '' : ' NOT');
        $sql = "$not EXISTS (".$this->subselect->toSql().')';

        return new Prepared($sql, "$not EXISTS subquery", false, 'boolean');
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
    }
}
