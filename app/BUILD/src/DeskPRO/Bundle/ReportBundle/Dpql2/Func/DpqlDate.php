<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Handler that wraps around DPQL_DATE() to provide a group fill.
 */
class DpqlDate extends AbstractDpqlFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if (count($arguments) != 1) {
            throw new DpqlException('DPQL_DATE() can only accept 1 argument.');
        }

        $expression = reset($arguments);
        $prepped    = $expression->prepare($statement, $section, $stack, $select, $metadata);

        $sql = 'DATE('.$prepped->sql().')';
        $res = new Prepared($sql, 'DPQL_DATE('.$prepped->name().')', false, 'date');

        $res->setGroupFill(function ($min, $max) {
            if (!$min && !$max) {
                return [];
            }

            $d = new \DateTime($min);
            $interval = $d->diff(new \DateTime($max));

            $fills = [];
            if ($interval->days) {
                $fills = [];
                for ($i = 0; $i < $interval->days; ++$i) {
                    $d->modify('+1 day');
                    $f = $d->format('Y-m-d');
                    $fills[] = [$f, $f, $f];
                }
            }

            return $fills;
        });

        return $res;
    }
}
