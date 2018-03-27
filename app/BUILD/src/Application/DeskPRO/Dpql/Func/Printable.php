<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Exception;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;

/**
 * This is used in SPLIT/GROUP BY clauses to ensure that the printed value
 * can be different than the split value. For example, PRINT(tickets.subject, tickets.id).
 */
class Printable extends AbstractFunc
{
    /**
     * Prepares the function for use, including validating that the usage is valid.
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
        if (!in_array($section, ['split', 'group'])) {
            throw new Exception('PRINT() may only be used in SPLIT BY and GROUP BY sections.');
        }

        if (count($this->_arguments) != 2) {
            throw new Exception('PRINT() can only accept 2 arguments');
        }

        $childStack = $stack;
        array_shift($childStack); // pop this off the stack - it doesn't exist to the children

        $sql   = reset($this->_arguments);
        $print = next($this->_arguments);

        $printPrepped = $print->prepare($statement, $section, $childStack, $select, $result);
        $sqlPrepped   = $sql->prepare($statement, $section, $childStack, $select, $result);

        return new Prepared(
            $sqlPrepped->sql(), $printPrepped->name(), $printPrepped->printed(), $printPrepped->renderer()
        );
    }
}
