<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

/**
 * Placeholder for today (00:00 - 23:59), based on the current person's time zone.
 */
class Today extends AbstractDateRange
{
    /**
     * {@inheritdoc}
     */
    public function getDateRange()
    {
        $today = $this->getDate()->format('Y-m-d');

        $beforeStart = new \DateTime("$today 23:59:59");
        $beforeStart->modify('-1 day');

        return [$today, "$today 00:00:00", "$today 23:59:59", $beforeStart->format('Y-m-d H:i:s')];
    }
}
