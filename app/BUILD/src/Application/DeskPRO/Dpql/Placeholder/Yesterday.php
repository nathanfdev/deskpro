<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Placeholder;

use Application\DeskPRO\App;

/**
 * Placeholder for yesterday (00:00 - 23:59), based on the current person's time zone.
 */
class Yesterday extends AbstractDateRange
{
    /**
     * Gets the date range components (printable, start, end).
     *
     * @return string[int]
     */
    protected function _getDateRange()
    {
        $tz   = App::getCurrentPerson()->getTimezone();
        $date = new \DateTime('-1 day', new \DateTimeZone($tz));

        $yesterday = $date->format('Y-m-d');

        return [$yesterday, "$yesterday 00:00:00", "$yesterday 23:59:59"];
    }
}
