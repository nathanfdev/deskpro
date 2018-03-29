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
 * Handler that wraps around DATE() to provide a group fill.
 */
class Date extends AbstractFunc
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
            throw new Exception('DATE() can only accept 1 argument.');
        }

        $expression = reset($this->_arguments);
        $prepped    = $expression->prepare($statement, $section, $stack, $select, $result);

        $sql = 'DATE('.$prepped->sql().')';
        $res = new Prepared($sql, 'DATE('.$prepped->name().')', false, 'date');

        $res->setGroupFill(function ($min, $max) {
            if (!$min && !$max) {
                return [];
            }

            $d = new \DateTime($min);
            $interval = $d->diff(new \DateTime($max));

            $fills = [];
            if ($interval->days) {
                $fills = [];
                for ($i = 0; $i < $interval->days; ++$i) {
                    $d->modify('+1 day');
                    $f = $d->format('Y-m-d');
                    $fills[] = [$f, $f, $f];
                }
            }

            return $fills;
        });

        return $res;
    }
}
