<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

/**
 * Place holder for the past 30 days.
 */
class Past6Months extends AbstractDateRange
{
    /**
     * {@inheritdoc}
     */
    public function getDateRange()
    {
        $date  = $this->getDate();
        $now   = $date->format('Y-m-d H:i:s');
        $today = $date->format('Y-m-d');

        $date->modify('-6 months');
        $beginning = $date->format('Y-m-d');

        $beforeStart = new \DateTime("$beginning 23:59:59");
        $beforeStart->modify('-1 day');

        return ["$beginning to $today", "$beginning 00:00:00", $now, $beforeStart->format('Y-m-d H:i:s')];
    }
}
