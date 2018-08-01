<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder\CustomDateRange;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Placeholder;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\StringPart;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractValueRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Handler that wraps around DPQL_MONTHNAME() to provide correct sorting if used in a group by.
 *
 * DPQL_MONTHNAME(fieldname, [format])
 * DPQL_MONTHNAME(fieldname, [format,] placeholder)
 * DPQL_MONTHNAME(fieldname, [format,] date1, date2)
 */
class DpqlMonthName extends AbstractDpqlFunc
{
    private $months = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
        7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
    ];
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

        if (isset($arguments[0]) && $arguments[0] instanceof Placeholder) {
            $place = $arguments[0]->getPlaceholder();
            array_shift($arguments);

            if ($place instanceof CustomDateRange) {
                $range     = $place->getDateRange();
                $placeArgs = [$range[1], $range[2]];
            } else {
                throw new DpqlException('DPQL_MONTHNAME() can only accept date range placeholders');
            }
        }

        $sql      = 'MONTH('.$prepped->sql().')';
        $renderer = function (AbstractValueRenderer $valueRenderer, $value, array $row, AbstractRenderer $renderer) use ($format) {
            $month = isset($this->months[$value]) ? $this->months[$value] : 'Unknown'; // this is for Mars probably, go Elon!
            if ($format === 'short') {
                $month = substr($month, 0, 3);
            }

            return $month;
        };
        $res = new Prepared($sql, 'DPQL_MONTHNAME('.$prepped->name().')', false, $renderer);

        $userMin = $userMax = null;
        if (count($arguments)) {
            $tmp = $this->_toLiteral($arguments[0]);
            if ($tmp) {
                try {
                    $userMin = \DateTime::createFromFormat('Y-m-d H:i:s', $tmp);
                } catch (\Exception $e) {
                    $userMin = null;
                }
            }
            $tmp = $this->_toLiteral($arguments[1]);
            if ($tmp) {
                try {
                    $userMax = \DateTime::createFromFormat('Y-m-d H:i:s', $tmp);
                } catch (\Exception $e) {
                    $userMax = null;
                }
            }
        } elseif ($placeArgs) {
            $userMin = \DateTime::createFromFormat('Y-m-d H:i:s', $placeArgs[0]);
            $userMax = \DateTime::createFromFormat('Y-m-d H:i:s', $placeArgs[1]);
        }

        $res->setGroupFill(function ($min, $max) use ($userMin, $userMax) {
            $fills = [];

            if ($userMin && $userMin < $min) {
                $min = $userMin;
            }
            if ($userMax && $userMax > $max) {
                $max = $userMax;
            }

            $cur = $min;
            while ($cur < $max) {
                $f = $cur;
                $fills[] = [$f, $f, $f];
                $cur += 1;
            }

            return $fills;
        });

        return $res;
    }
}
