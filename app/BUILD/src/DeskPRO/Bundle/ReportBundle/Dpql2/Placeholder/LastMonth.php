<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

/**
 * Place holder for the previous month based on the current person's time zone.
 */
class LastMonth extends AbstractDateRange
{
    /**
     * {@inheritdoc}
     */
    public function getDateRange()
    {
        $date      = $this->getDate();
        $thisMonth = $date->format('Y-m');

        $endDate = new \DateTime("$thisMonth-01", $date->getTimezone());
        $endDate->modify('-1 day');

        $endDateValue   = $endDate->format('Y-m-d');
        $startDateValue = $endDate->format('Y-m').'-01';

        return ["$startDateValue to $endDateValue", "$startDateValue 00:00:00", "$endDateValue 23:59:59"];
    }
}
