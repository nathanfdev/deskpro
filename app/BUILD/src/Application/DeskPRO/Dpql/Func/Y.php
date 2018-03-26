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
 * Used in the GROUP BY clause to specify columns that will be grouped in the Y direction in a matrix table.
 */
class Y extends AbstractFunc
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
        if ($section != 'group') {
            throw new Exception('Y() may only be used in GROUP BY.');
        }
        if (count($stack) > 1) {
            // note: the top of the stack is this function
            throw new Exception('Y() may only be used at the top-level.');
        }

        $childStack = $stack;
        array_shift($childStack); // pop this off the stack - it doesn't exist to the children

        foreach ($this->_arguments as $arg) {
            if ($arg instanceof \Application\DeskPRO\Dpql\Statement\Part\NullValue) {
                continue;
            }

            $groupBy = $arg->prepare($statement, $section, $childStack, $select, $result);
            if ($groupBy->hasValue()) {
                $printId = $select->addSelectField($groupBy->printed());
                $select->addGroupBy($groupBy->sql());
                $defaultOrder = $statement->addDefaultOrder($groupBy->printed());

                if ($groupBy->printed() === $groupBy->sql()) {
                    $groupId = $printId;
                } else {
                    $groupId = $select->addSelectField($groupBy->sql());
                }

                if ($groupBy->ordered() == $groupBy->printed()) {
                    $orderId = $printId;
                } else {
                    $orderId = $select->addSelectField($groupBy->ordered());
                }

                if ($defaultOrder && $groupBy->groupFill()) {
                    $statement->addGroupFill($groupBy->groupFill(), $printId, $groupId, $orderId);
                }

                $result->addGroupYColumn($groupBy->name(), $groupId, $printId, $groupBy->renderer());
            }
        }

        return new Prepared(false);
    }
}
