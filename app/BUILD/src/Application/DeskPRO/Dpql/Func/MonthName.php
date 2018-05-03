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
 * Handler that wraps around MONTHNAME() to provide correct sorting if used in a group by.
 */
class MonthName extends AbstractFunc
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
            throw new Exception('MONTHNAME() can only accept 1 argument.');
        }

        $expression = reset($this->_arguments);
        $prepped    = $expression->prepare($statement, $section, $stack, $select, $result);

        $sql      = 'MONTH('.$prepped->sql().')';
        $renderer = function (AbstractValues $valueRenderer, $value, array $row, AbstractRenderer $renderer) {
            switch ($value) {
                case 1: return 'January';
                case 2: return 'February';
                case 3: return 'March';
                case 4: return 'April';
                case 5: return 'May';
                case 6: return 'June';
                case 7: return 'July';
                case 8: return 'August';
                case 9: return 'September';
                case 10: return 'October';
                case 11: return 'November';
                case 12: return 'December';
            }
        };
        $res = new Prepared($sql, 'MONTHNAME('.$prepped->name().')', false, $renderer);

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
