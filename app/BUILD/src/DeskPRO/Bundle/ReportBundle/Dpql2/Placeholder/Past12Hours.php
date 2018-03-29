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
    protected function getDateRange()
    {
        $date  = $this->getDate();
        $now   = $date->format('Y-m-d H:i:s');
        $today = $date->format('Y-m-d H:i:s');

        $date->modify('-12 hours');
        $beginning = $date->format('Y-m-d H:i:s');

        return ["$beginning to $today", $beginning, $now];
    }
}
