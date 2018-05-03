<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Placeholder;

use Application\DeskPRO\App;

/**
 * Placeholder for today (00:00 - 23:59), based on the current person's time zone.
 */
class Today extends AbstractDateRange
{
    /**
     * Gets the date range components (printable, start, end).
     *
     * @return string[int]
     */
    protected function _getDateRange()
    {
        $tz    = App::getCurrentPerson()->getTimezone();
        $date  = new \DateTime('now', new \DateTimeZone($tz));
        $today = $date->format('Y-m-d');

        return [$today, "$today 00:00:00", "$today 23:59:59"];
    }
}
