<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractValueRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Handler that wraps around DPQL_DAYNAME() to provide correct sorting if used in a group by.
 */
class DpqlDayName extends AbstractDpqlFunc
{
    public static function getName()
    {
        return 'DPQL_DAYNAME';
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if (count($arguments) != 1) {
            throw new DpqlException('DPQL_DAYNAME() can only accept 1 argument.');
        }

        $expression = reset($arguments);
        $prepped    = $expression->prepare($statement, $section, $stack, $select, $metadata);

        $sql      = 'DAYOFWEEK('.$prepped->sql().')';
        $renderer = function (AbstractValueRenderer $valueRenderer, $value, array $row, AbstractRenderer $renderer) {
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

        $res = new Prepared($sql, 'DPQL_DAYNAME('.$prepped->name().')', false, $renderer);

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
