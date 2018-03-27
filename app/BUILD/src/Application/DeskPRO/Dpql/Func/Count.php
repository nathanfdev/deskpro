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
 * Handler for COUNT() DPQL function calls, which can work like COUNT(*) with
 * no arguments, but can also take an argument and only count those rows that
 * match the argument.
 */
class Count extends AbstractFunc
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
        if (!in_array($section, ['select', 'split', 'group', 'order'])) {
            throw new Exception('COUNT() may only be used in SELECT, SPLIT BY, GROUP BY, and ORDER BY sections.');
        }

        if (!$this->_arguments) {
            $res = new Prepared('COUNT(*)', 'COUNT()', false, 'number');
        } else {
            if (count($this->_arguments) > 1) {
                throw new Exception('COUNT() can only accept 0 or 1 argument');
            }

            $condition = reset($this->_arguments);
            $prepped   = $condition->prepare($statement, $section, $stack, $select, $result);

            $sql = 'SUM(IF('.$prepped->sql().', 1, 0))';
            $res = new Prepared($sql, 'COUNT('.$prepped->name().')', false, 'number');
        }

        return $res;
    }
}
