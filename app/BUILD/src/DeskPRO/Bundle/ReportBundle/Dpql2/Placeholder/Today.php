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
    protected function getDateRange()
    {
        $today = $this->getDate()->format('Y-m-d');

        return [$today, "$today 00:00:00", "$today 23:59:59"];
    }
}
