<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

/**
 * Placeholder for the current week (first to last day), based on the current person's time zone.
 */
class ThisWeek extends AbstractDateRange
{
    /**
     * {@inheritdoc}
     */
    public function getDateRange()
    {
        $person = $this->getPerson();
        $date   = $this->getDate();

        // find start of this week
        $currentDayOfWeek = $date->format('N');
        $startAdjust      = $currentDayOfWeek - ($person ? $person->getStartOfWeek() : 1);

        if ($startAdjust) {
            if ($startAdjust > 0) {
                $date->modify('-'.$startAdjust.' days');
            } else {
                $date->modify('-'.(7 + $startAdjust).' days');
            }
        }

        $start = $date->format('Y-m-d');

        $date->modify('+6 days'); // 7 days will take us to the next start of the week
        $end = $date->format('Y-m-d');

        $beforeStart = new \DateTime("$start 23:59:59");
        $beforeStart->modify('-1 day');

        return ["$start to $end", "$start 00:00:00", "$end 23:59:59", $beforeStart->format('Y-m-d H:i:s')];
    }
}
