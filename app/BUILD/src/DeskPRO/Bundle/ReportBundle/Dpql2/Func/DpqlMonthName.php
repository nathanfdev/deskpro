<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\StringPart;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractValueRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Handler that wraps around DPQL_MONTHNAME() to provide correct sorting if used in a group by.
 *
 * DPQL_MONTHNAME(fieldname [, format])
 */
class DpqlMonthName extends AbstractDpqlFunc
{
    public static function getName()
    {
        return 'DPQL_MONTHNAME';
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if (count($arguments) < 1) {
            throw new DpqlException('DPQL_MONTHNAME() needs at least one param (the field)');
        }

        $expression = array_shift($arguments);
        $placeArgs  = null;
        $prepped    = $expression->prepare($statement, $section, $stack, $select, $metadata);

        if (isset($arguments[0]) && $arguments[0] instanceof StringPart) {
            $format = $arguments[0]->string;
            array_shift($arguments);
        } else {
            $format = 'long';
        }

        $sql      = 'MONTH('.$prepped->sql().')';
        $renderer = function (AbstractValueRenderer $valueRenderer, $value, array $row, AbstractRenderer $renderer) use ($format) {
            $format = $format === 'short' ? 'M' : 'F';

            return \DateTime::createFromFormat('!m', $value)->format($format);
        };
        $res = new Prepared($sql, 'DPQL_MONTHNAME('.$prepped->name().')', false, $renderer);

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
