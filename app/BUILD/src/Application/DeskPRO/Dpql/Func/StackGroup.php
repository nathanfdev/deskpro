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
 * Handler for STACK_GROUP function.
 */
class StackGroup extends AbstractFunc
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
        if (count($this->_arguments) != 2) {
            throw new Exception('STACK_GROUP() can only accept 2 arguments.');
        }

        $childStack = $stack;
        array_shift($childStack); // pop this off the stack - it doesn't exist to the children

        $expression = reset($this->_arguments);
        $prepped    = $expression->prepare($statement, $section, $childStack, $select, $result);

        if ($section == 'group' && !$childStack) {
            $grouper      = next($this->_arguments);
            $preppedGroup = $grouper->prepare($statement, $section, $childStack, $select, $result);

            if ($preppedGroup->hasValue()) {
                $printId = $statement->addSqlSelectField($preppedGroup->printed());

                if ($preppedGroup->printed() === $preppedGroup->sql()) {
                    $groupId = $printId;
                } else {
                    $groupId = $statement->addSelectField($preppedGroup->sql());
                }

                $result->addGroupStackColumn($groupId, $printId);
            }

            $sql = $prepped->sql();

            return new Prepared($sql, 'STACK_GROUP('.$prepped->name().', '.$preppedGroup->name().')', $prepped->printed());
        } else {
            return $prepped;
        }
    }
}
