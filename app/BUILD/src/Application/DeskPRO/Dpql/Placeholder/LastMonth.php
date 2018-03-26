<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Placeholder;

use Application\DeskPRO\App;

/**
 * Place holder for the previous month based on the current person's time zone.
 */
class LastMonth extends AbstractDateRange
{
    /**
     * Gets the date range components (printable, start, end).
     *
     * @return string[int]
     */
    protected function _getDateRange()
    {
        $tz = new \DateTimeZone(App::getCurrentPerson()->getTimezone());

        $date      = new \DateTime('now', $tz);
        $thisMonth = $date->format('Y-m');

        $endDate = new \DateTime("$thisMonth-01", $tz);
        $endDate->modify('-1 day');

        $endDateValue   = $endDate->format('Y-m-d');
        $startDateValue = $endDate->format('Y-m').'-01';

        return ["$startDateValue to $endDateValue", "$startDateValue 00:00:00", "$endDateValue 23:59:59"];
    }
}
