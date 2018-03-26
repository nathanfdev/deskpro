<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Display;

/**
 * Represents a call to the IN/NOT IN operator.
 */
class In extends AbstractPart
{
    /**
     * @var \Application\DeskPRO\Dpql\Statement\Part\AbstractPart
     */
    public $lhs;

    /**
     * @var \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[]
     */
    public $values;

    /**
     * True = IN, false = NOT IN.
     *
     * @var bool
     */
    public $positive;

    /**
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart   $lhs
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[] $values
     * @param bool                                                    $positive
     */
    public function __construct(AbstractPart $lhs, array $values, $positive = true)
    {
        $this->lhs      = $lhs;
        $this->values   = $values;
        $this->positive = $positive;
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
        $values = [];
        foreach ($this->values as $value) {
            $values[] = $value->toDpql($statement, $section, $stack);
        }

        $not = ($this->positive ? '' : ' NOT');

        return $this->lhs->toDpql($statement, $section, $stack).$not.' IN ('.implode(', ', $values).')';
    }
}
