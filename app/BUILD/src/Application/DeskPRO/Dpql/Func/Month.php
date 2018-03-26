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
 * Handler that wraps around MONTH() to provide a group fill.
 */
class Month extends AbstractFunc
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
            throw new Exception('MONTH() can only accept 1 argument.');
        }

        $expression = reset($this->_arguments);
        $prepped    = $expression->prepare($statement, $section, $stack, $select, $result);

        $sql = 'MONTH('.$prepped->sql().')';
        $res = new Prepared($sql, 'MONTH('.$prepped->name().')', false, 'numberraw');

        $res->setGroupFill(function ($min, $max) {
            $fills = [];
            for ($i = $min; $i <= $max; ++$i) {
                $fills[] = [$i, $i, $i];
            }

            return $fills;
        });

        return $res;
    }
}
