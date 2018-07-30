<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

/**
 * Placeholder for the current month (first to last day), based on the current person's time zone.
 */
class ThisMonth extends AbstractDateRange
{
    /**
     * {@inheritdoc}
     */
    public function getDateRange()
    {
        $date  = $this->getDate();
        $start = $date->format('Y-m');

        $endDate = new \DateTime("$start-01", $date->getTimezone());
        $endDate->modify('+1 month')->modify('-1 day');

        $endDateValue = $endDate->format('Y-m-d');

        $beforeStart = new \DateTime("$start-01 23:59:59");
        $beforeStart->modify('-1 day');

        return ["$start-01 to $endDateValue", "$start-01 00:00:00", "$endDateValue 23:59:59", $beforeStart->format('Y-m-d H:i:s')];
    }
}
