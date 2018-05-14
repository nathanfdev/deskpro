<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Number;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\StringPart;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractValueRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Handler that wraps around DPQL_HOUR() to provide a group fill.
 */
class DpqlHour extends AbstractDpqlFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if (count($arguments) > 3) {
            throw new DpqlException('DPQL_HOUR() can only accept 1, 2 or 3 arguments.');
        }

        $expression = reset($arguments);
        $prepped    = $expression->prepare($statement, $section, $stack, $select, $metadata);

        $tzOffsetSeconds = $statement->getTimezoneOffsetForFunction($stack);
        $sql             = "HOUR({$prepped->sql()} + INTERVAL $tzOffsetSeconds SECOND)";

        $renderer = function (AbstractValueRenderer $valueRenderer, $value, array $row, AbstractRenderer $renderer) {
            $ampm = $value >= 12 ? 'pm' : 'am';
            $hour = $value % 12 ?: 12;

            return "{$hour}{$ampm}";
        };

        $res = new Prepared($sql, 'DPQL_HOUR('.$prepped->name().')', false, $renderer);

        $userMin = $userMax = null;
        if (isset($arguments[1]) && ($arguments[1] instanceof Number || $arguments[1] instanceof StringPart)) {
            $userMin = $arguments[1]->getValue();
        }
        if (isset($arguments[2]) && ($arguments[2] instanceof Number || $arguments[2] instanceof StringPart)) {
            $userMax = $arguments[2]->getValue();
        }

        $res->setGroupFill(function ($min, $max) use ($userMin, $userMax) {
            if (!$min) {
                $min = 0;
            }
            if (!$max) {
                $max = 23;
            }

            if ($userMin !== null) {
                $min = $userMin;
            }
            if ($userMax !== null) {
                $max = $userMax;
            }

            $fills = [];
            for ($i = $min; $i < $max; ++$i) {
                $fills[] = [$i, $i, $i];
            }

            return $fills;
        });

        return $res;
    }
}
