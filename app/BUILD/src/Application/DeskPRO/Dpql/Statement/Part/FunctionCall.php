<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Func\AbstractFunc;
use Application\DeskPRO\Dpql\Statement\Display;

/**
 * Represents a call to a DPQL function.
 */
class FunctionCall extends AbstractPart
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[]
     */
    public $arguments;

    /**
     * @param string                                                  $name
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[] $arguments
     */
    public function __construct($name, array $arguments = [])
    {
        $this->name      = $name;
        $this->arguments = $arguments;
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

        $func = AbstractFunc::create($this->name, $this->arguments);

        return $func->prepare($statement, $section, $childStack, $select, $result);
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
        $arguments = [];
        foreach ($this->arguments as $argument) {
            $arguments[] = $argument->toDpql($statement, $section, $stack);
        }

        return $this->name.'('.implode(', ', $arguments).')';
    }
}
