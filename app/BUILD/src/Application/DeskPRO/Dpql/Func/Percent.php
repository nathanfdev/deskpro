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
 * Gets the percentage of all rows in the group that match the given argument.
 * For example, PERCENT(table.column > 10).
 */
class Percent extends AbstractFunc
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
            throw new Exception('PERCENT() may only be used in SELECT, SPLIT BY, GROUP BY, and ORDER BY sections.');
        }

        if (!in_array(count($this->_arguments), [1, 2])) {
            throw new Exception('PERCENT() can only accept 1 or 2 arguments');
        }

        $condition = reset($this->_arguments);
        $renderer  = 'percent';
        if (isset($this->arguments[1]) && $this->_arguments[1] instanceof Dpql\Statement\Part\Number && $this->_arguments[1]->getValue() ===
            1) {
            $renderer = 'percentfull';
        }
        $prepped = $condition->prepare($statement, $section, $stack, $select, $result);

        $sql = 'IF(COUNT(*) > 0, (SUM(IF('.$prepped->sql().', 1, 0)) / COUNT(*)) * 100, 0)';

        return new Prepared($sql, 'PERCENT('.$prepped->name().')', false, $renderer);
    }
}
