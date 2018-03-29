<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Placeholder;

use Application\DeskPRO\App;

/**
 * Place holder for the past week (today - 1 week), based on the current person's time zone.
 */
class Past7Days extends AbstractDateRange
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

        $date->modify('-7 days');
        $beginning = $date->format('Y-m-d');

        return ["$beginning to $today", "$beginning 00:00:00", $now];
    }
}
