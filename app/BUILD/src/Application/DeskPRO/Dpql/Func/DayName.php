<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Exception;
use Application\DeskPRO\Dpql\Renderer\AbstractRenderer;
use Application\DeskPRO\Dpql\Renderer\Values\AbstractValues;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;

/**
 * Handler that wraps around DAYNAME() to provide correct sorting if used in a group by.
 */
class DayName extends AbstractFunc
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
            throw new Exception('DAYNAME() can only accept 1 argument.');
        }

        $expression = reset($this->_arguments);
        $prepped    = $expression->prepare($statement, $section, $stack, $select, $result);

        $sql      = 'DAYOFWEEK('.$prepped->sql().')';
        $renderer = function (AbstractValues $valueRenderer, $value, array $row, AbstractRenderer $renderer) {
            switch ($value) {
                case 1: return 'Sunday';
                case 2: return 'Monday';
                case 3: return 'Tuesday';
                case 4: return 'Wednesday';
                case 5: return 'Thursday';
                case 6: return 'Friday';
                case 7: return 'Saturday';
            }
        };

        $res = new Prepared($sql, 'DAYNAME('.$prepped->name().')', false, $renderer);

        $res->setGroupFill(function ($min, $max) {
            if ($min == $max) {
                return [];
            }

            $fills = [];
            for ($i = $min; $i <= $max; ++$i) {
                $fills[] = [$i, $i, $i];
            }

            return $fills;
        });

        return $res;
    }
}
