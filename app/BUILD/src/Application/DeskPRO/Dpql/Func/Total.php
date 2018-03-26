<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Exception;
use Application\DeskPRO\Dpql\Statement\Display;

/**
 * Handler for TOTAL function.
 */
class Total extends AbstractFunc
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
        if ($section != 'select') {
            throw new Exception('TOTAL() may only be used in SELECT.');
        }
        if (count($this->_arguments) != 1) {
            throw new Exception('TOTAL() can only accept 1 argument.');
        }
        if (count($stack) > 1) {
            // note: the top of the stack is this function
            throw new Exception('TOTAL() may only be used at the top-level.');
        }

        $childStack = $stack;
        array_shift($childStack); // pop this off the stack - it doesn't exist to the children

        $expression = reset($this->_arguments);
        $prepped    = $expression->prepare($statement, $section, $stack, $select, $result);
        $prepped->setTotal(true);

        return $prepped;
    }
}
