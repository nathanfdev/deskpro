<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

/**
 * Place holder for the past week (today - 1 week), based on the current person's time zone.
 */
class Past7Days extends AbstractDateRange
{
    /**
     * {@inheritdoc}
     */
    public function getDateRange()
    {
        $date  = $this->getDate();
        $now   = $date->format('Y-m-d H:i:s');
        $today = $date->format('Y-m-d');

        $date->modify('-7 days');
        $beginning = $date->format('Y-m-d');

        return ["$beginning to $today", "$beginning 00:00:00", $now];
    }
}
