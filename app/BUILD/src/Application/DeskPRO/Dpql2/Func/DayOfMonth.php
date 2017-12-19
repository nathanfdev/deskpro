<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql2\Func;

use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql2\Exception;
use Application\DeskPRO\Dpql2\Renderer\AbstractRenderer;
use Application\DeskPRO\Dpql2\Renderer\Values\AbstractValues;
use Application\DeskPRO\Dpql2\Statement\Part\Prepared;
use Application\DeskPRO\Dpql2\Statement\SelectPart;

/**
 * Handler that wraps around DAYOFMONTH() to add ordinal suffixes.
 */
class DayOfMonth extends AbstractFunc
{
    /**
     * Prepares the function for use, including validating that the usage is valid.
     *
     * @param \Application\DeskPRO\Dpql2\Statement\SelectPart          $statement
     * @param string                                                   $section   Name of the section usage is in (select, where, split, group, order)
     * @param \Application\DeskPRO\Dpql2\Statement\Part\AbstractPart[] $stack     Parent parts
     * @param \Application\DeskPRO\Dpql2\SqlSelect                     $select    Select being built up
     * @param \Application\DeskPRO\Dpql2\ResultHandler                 $result
     *
     * @throws \Application\DeskPRO\Dpql2\Exception
     *
     * @return \Application\DeskPRO\Dpql2\Statement\Part\Prepared|bool Prepared results or false if there's no output
     */
    public function prepare(
        SelectPart $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
    ) {
        if (count($this->_arguments) != 1) {
            throw new Exception('DAYOFMONTH() can only accept 1 argument.');
        }

        $expression = reset($this->_arguments);
        $prepped    = $expression->prepare($statement, $section, $stack, $select, $result);

        $sql      = 'DAYOFMONTH('.$prepped->sql().')';
        $renderer = function (AbstractValues $valueRenderer, $value, array $row, AbstractRenderer $renderer) {
            $mod = $value % 100;
            switch ($mod) {
                case 11:
                case 12:
                case 13:
                    return $value.'th';

                default:
                    $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];

                    return $value.$ends[$value % 10];
            }
        };

        $res = new Prepared($sql, 'DAYOFMONTH('.$prepped->name().')', false, $renderer);

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
