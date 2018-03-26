<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Placeholder;

use Application\DeskPRO\App;

/**
 * Placeholder for the current month (first to last day), based on the current person's time zone.
 */
class ThisMonth extends AbstractDateRange
{
    /**
     * Gets the date range components (printable, start, end).
     *
     * @return string[int]
     */
    protected function _getDateRange()
    {
        $tz   = new \DateTimeZone(App::getCurrentPerson()->getTimezone());
        $date = new \DateTime('now', $tz);

        $start = $date->format('Y-m');

        $endDate = new \DateTime("$start-01", $tz);
        $endDate->modify('+1 month')->modify('-1 day');

        $endDateValue = $endDate->format('Y-m-d');

        return ["$start-01 to $endDateValue", "$start-01 00:00:00", "$endDateValue 23:59:59"];
    }
}
