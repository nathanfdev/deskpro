<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Display;

/**
 * Represents an expression where the user explicitly put in parentheses.
 */
class Parentheses extends AbstractPart
{
    /**
     * @var \Application\DeskPRO\Dpql\Statement\Part\AbstractPart
     */
    public $expression;

    /**
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart $expression
     */
    public function __construct(AbstractPart $expression)
    {
        $this->expression = $expression;
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
        $prepared = $this->expression->prepare($statement, $section, $stack, $select, $result);
        $prepared->setName('('.$prepared->name().')');

        return $prepared;
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
        return '('.$this->expression->toDpql($statement, $section, $stack).')';
    }
}
