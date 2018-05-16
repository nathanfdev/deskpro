<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Represents an expression where the user explicitly put in parentheses.
 */
class Parentheses extends AbstractPart
{
    /**
     * @var AbstractPart
     */
    public $expression;

    /**
     * Constructor.
     *
     * @param AbstractPart $expression
     */
    public function __construct(AbstractPart $expression)
    {
        $this->expression = $expression;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        $prepared = $this->expression->prepare($statement, $section, $stack, $select, $metadata);
        $prepared->setName('('.$prepared->name().')');

        return $prepared;
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        return '('.$this->expression->toDpql($statement, $section, $stack).')';
    }
}
