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

        $sql      = $prepped->sql();
        $renderer = function (AbstractValueRenderer $valueRenderer, $value, array $row, AbstractRenderer $renderer) use ($format) {
            try {
                $d = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
            } catch (\Exception $e) {
                return 'Invalid';
            }

            if ($format === 'short') {
                switch ($d->format('n')) {
                    case 1:
                        return 'Jan';
                    case 2:
                        return 'Feb';
                    case 3:
                        return 'Mar';
                    case 4:
                        return 'Apr';
                    case 5:
                        return 'May';
                    case 6:
                        return 'Jun';
                    case 7:
                        return 'Jul';
                    case 8:
                        return 'Aug';
                    case 9:
                        return 'Sep';
                    case 10:
                        return 'Oct';
                    case 11:
                        return 'Nov';
                    case 12:
                        return 'Dec';
                }
            } else {
                switch ($d->format('n')) {
                    case 1:
                        return 'January';
                    case 2:
                        return 'February';
                    case 3:
                        return 'March';
                    case 4:
                        return 'April';
                    case 5:
                        return 'May';
                    case 6:
                        return 'June';
                    case 7:
                        return 'July';
                    case 8:
                        return 'August';
                    case 9:
                        return 'September';
                    case 10:
                        return 'October';
                    case 11:
                        return 'November';
                    case 12:
                        return 'December';
                }
            }

            return;
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

            $min = \DateTime::createFromFormat('Y-m-d H:i:s', $min);
            $max = \DateTime::createFromFormat('Y-m-d H:i:s', $max);

            if ($userMin && $userMin < $min) {
                $min = $userMin;
            }
            if ($userMax && $userMax > $max) {
                $max = $userMax;
            }

            $cur = clone $min;
            while ($cur < $max) {
                $f = $cur->format('Y-m-d 00:00:00');
                $fills[] = [$f, $f, $f];
                $cur->modify('+1 month');
            }

            return $fills;
        });

        return $res;
    }
}
