<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

/**
 * Place holder for the last 1 hour.
 */
class PastHour extends AbstractDateRange
{
    /**
     * {@inheritdoc}
     */
    protected function getDateRange()
    {
        $date  = $this->getDate();
        $now   = $date->format('Y-m-d H:i:s');
        $today = $date->format('Y-m-d H:i:s');

        $date->modify('-1 hour');
        $beginning = $date->format('Y-m-d H:i:s');

        return ["$beginning to $today", $beginning, $now];
    }
}
