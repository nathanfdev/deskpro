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
 * Converts the date within this from the person's time zone to UTC by undoing the adjustment.
 */
class ToUtc extends AbstractFunc
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
        if (count($this->_arguments) != 1) {
            throw new Exception('TO_UTC() can only accept 1 argument');
        }

        $arg = reset($this->_arguments);

        $argPrepared = $arg->prepare($statement, $section, $stack, $select, $result);

        $tzOffsetSeconds = $statement->getTimezoneOffsetForFunction($stack);
        $interval        = ($tzOffsetSeconds ? " - INTERVAL $tzOffsetSeconds SECOND" : '');

        return new Prepared("({$argPrepared->sql()}$interval)", "TO_UTC({$argPrepared->name()})", false, 'datetime');
    }
}
