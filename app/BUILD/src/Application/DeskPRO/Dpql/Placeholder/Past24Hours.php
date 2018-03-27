<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Placeholder;

use Application\DeskPRO\App;

/**
 * Place holder for the 24 hours, based on the current person's time zone.
 */
class Past24Hours extends AbstractDateRange
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

        $now   = $date->format('Y-m-d H:i:s');
        $today = $date->format('Y-m-d');

        $date->modify('-1 day');
        $beginning = $date->format('Y-m-d H:i:s');

        return ["$beginning to $today", "$beginning", $now];
    }
}
