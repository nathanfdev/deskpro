<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

/**
 * Place holder for the last 12 hours.
 */
class Past12Hours extends AbstractDateRange
{
    /**
     * {@inheritdoc}
     */
    public function getDateRange()
    {
        $date  = $this->getDate();
        $now   = $date->format('Y-m-d H:i:s');
        $today = $date->format('Y-m-d H:i:s');

        $date->modify('-12 hours');
        $beginning = $date->format('Y-m-d H:i:s');

        $beforeStart = new \DateTime("$beginning 23:59:59");
        $beforeStart->modify('-1 day');

        return ["$beginning to $today", $beginning, $now, $beforeStart->format('Y-m-d H:i:s')];
    }
}
