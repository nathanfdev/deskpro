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

namespace DeskPRO\Bundle\AppBundle\Dpql2\Func;

use DeskPRO\Bundle\AppBundle\Dpql2\Exception;
use DeskPRO\Bundle\AppBundle\Dpql2\Renderer\AbstractRenderer;
use DeskPRO\Bundle\AppBundle\Dpql2\Renderer\Values\AbstractValues;
use DeskPRO\Bundle\AppBundle\Dpql2\ResultHandler;
use DeskPRO\Bundle\AppBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\AppBundle\Dpql2\Statement\SelectPart;

/**
 * Handler that wraps around DAYNAME() to provide correct sorting if used in a group by.
 */
class DayName extends AbstractFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultHandler $result)
    {
        if (count($arguments) != 1) {
            throw new Exception('DAYNAME() can only accept 1 argument.');
        }

        $expression = reset($arguments);
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
