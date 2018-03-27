<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Placeholder;

use Application\DeskPRO\App;

/**
 * Place holder for the last 12 hours.
 */
class Past12Hours extends AbstractDateRange
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
        $today = $date->format('Y-m-d H:i:s');

        $date->modify('-12 hours');
        $beginning = $date->format('Y-m-d H:i:s');

        return ["$beginning to $today", $beginning, $now];
    }
}
