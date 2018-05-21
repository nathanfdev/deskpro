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
 * Handler that wraps around DPQL_DAYOFMONTH() to add ordinal suffixes.
 */
class DpqlDayOfMonth extends AbstractDpqlFunc
{
    public static function getName()
    {
        return 'DPQL_DAYOFMONTH';
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if (count($arguments) != 1) {
            throw new DpqlException('DPQL_DAYOFMONTH() can only accept 1 argument.');
        }

        $expression = reset($arguments);
        $prepped    = $expression->prepare($statement, $section, $stack, $select, $metadata);

        $sql      = 'DAYOFMONTH('.$prepped->sql().')';
        $renderer = function (AbstractValueRenderer $valueRenderer, $value, array $row, AbstractRenderer $renderer) {
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

        $res = new Prepared($sql, 'DPQL_DAYOFMONTH('.$prepped->name().')', false, $renderer);

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
