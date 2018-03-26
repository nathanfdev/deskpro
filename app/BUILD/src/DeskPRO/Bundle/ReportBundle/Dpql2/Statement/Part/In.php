<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Represents a call to the IN/NOT IN operator.
 */
class In extends AbstractPart
{
    /**
     * @var AbstractPart
     */
    public $lhs;

    /**
     * @var AbstractPart[]
     */
    public $values;

    /**
     * True = IN, false = NOT IN.
     *
     * @var bool
     */
    public $positive;

    /**
     * Constructor.
     *
     * @param AbstractPart   $lhs
     * @param AbstractPart[] $values
     * @param bool           $positive
     */
    public function __construct(AbstractPart $lhs, array $values, $positive = true)
    {
        $this->lhs      = $lhs;
        $this->values   = $values;
        $this->positive = $positive;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result)
    {
        $childStack = $this->getChildStack($stack);

        $lhs = $this->lhs->prepare($statement, $section, $childStack, $select, $result);
        $not = ($this->positive ? '' : ' NOT');

        $valuesSql  = [];
        $valuesName = [];
        foreach ($this->values as $value) {
            $prepped      = $value->prepare($statement, $section, $childStack, $select, $result);
            $valuesSql[]  = $prepped->sql();
            $valuesName[] = $prepped->name();
        }

        $sql = "{$lhs->sql()}$not IN (".implode(', ', $valuesSql).')';

        return new Prepared($sql, "{$lhs->name()}$not IN (".implode(', ', $valuesName).')', false, 'boolean');
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        $values = [];
        foreach ($this->values as $value) {
            $values[] = $value->toDpql($statement, $section, $stack);
        }

        $not = ($this->positive ? '' : ' NOT');

        return $this->lhs->toDpql($statement, $section, $stack).$not.' IN ('.implode(', ', $values).')';
    }
}
