<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Represents an alias (X AS Y) part of a DPQL statement.
 */
class Alias extends AbstractPart
{
    /**
     * @var AbstractPart
     */
    public $value;

    /**
     * @var string
     */
    public $alias;

    /**
     * Constructor.
     *
     * @param SelectPart|AbstractPart $value
     * @param string                  $alias
     */
    public function __construct($value, $alias)
    {
        $this->value = $value;
        $this->alias = $alias;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result)
    {
        if ($section !== 'from') {
            throw new DpqlException('Alias prepare() cannot not be called');
        }

        if ($this->value instanceof SelectPart) {
            $this->value->prepare();

            return new Prepared('('.$this->value->toSql().')', $this->alias);
        } else {
            $childStack = $this->getChildStack($stack);
            $value      = $this->value->prepare($statement, $section, $childStack, $select, $result);

            return new Prepared('('.$value->sql().')', $this->alias);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        return $this->value->toDpql($statement, $section, $stack).' AS '.$statement->quoteDpqlString($this->alias);
    }
}
