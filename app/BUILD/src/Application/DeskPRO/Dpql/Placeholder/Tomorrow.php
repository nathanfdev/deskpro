<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Placeholder;

use Application\DeskPRO\App;

/**
 * Placeholder for tomorrow (00:00 - 23:59), based on the current person's time zone.
 */
class Tomorrow extends AbstractDateRange
{
    /**
     * Gets the date range components (printable, start, end).
     *
     * @return string[int]
     */
    protected function _getDateRange()
    {
        $tz   = App::getCurrentPerson()->getTimezone();
        $date = new \DateTime('+1 day', new \DateTimeZone($tz));

        $day = $date->format('Y-m-d');

        return [$day, "$day 00:00:00", "$day 23:59:59"];
    }
}
