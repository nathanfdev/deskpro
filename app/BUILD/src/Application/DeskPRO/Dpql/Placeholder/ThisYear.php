<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Placeholder;

use Application\DeskPRO\App;

/**
 * Placeholder for the current year (first to last day), based on the current person's time zone.
 */
class ThisYear extends AbstractDateRange
{
    /**
     * Gets the date range components (printable, start, end).
     *
     * @return string[int]
     */
    protected function _getDateRange()
    {
        $tz   = App::getCurrentPerson()->getTimezone();
        $date = new \DateTime('now', new \DateTimeZone($tz));

        $year = $date->format('Y');

        return [$year, "$year-01-01 00:00:00", "$year-12-31 23:59:59"];
    }
}
