<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

/**
 * Place holder for the 24 hours, based on the current person's time zone.
 */
class Past24Hours extends AbstractDateRange
{
    /**
     * {@inheritdoc}
     */
    public function getDateRange()
    {
        $date  = $this->getDate();
        $now   = $date->format('Y-m-d H:i:s');
        $today = $date->format('Y-m-d');

        $date->modify('-1 day');
        $beginning = $date->format('Y-m-d H:i:s');

        $beforeStart = new \DateTime("$beginning");
        $beforeStart->modify('-1 second');

        return ["$beginning to $today", $beginning, $now, $beforeStart->format('Y-m-d H:i:s')];
    }
}
