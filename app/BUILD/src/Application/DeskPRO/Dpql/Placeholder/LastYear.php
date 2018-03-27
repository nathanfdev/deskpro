<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Placeholder;

use Application\DeskPRO\App;

/**
 * Place holder for the previous year based on the current person's time zone.
 */
class LastYear extends AbstractDateRange
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

        $year = $date->format('Y') - 1;

        return [$year, "$year-01-01 00:00:00", "$year-12-31 23:59:59"];
    }
}
