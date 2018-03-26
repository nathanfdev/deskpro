<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

/**
 * Placeholder for yesterday (00:00 - 23:59), based on the current person's time zone.
 */
class Yesterday extends AbstractDateRange
{
    /**
     * {@inheritdoc}
     */
    protected function getDateRange()
    {
        $date = $this->getDate();
        $date->modify('-1 day');

        $yesterday = $date->format('Y-m-d');

        return [$yesterday, "$yesterday 00:00:00", "$yesterday 23:59:59"];
    }
}
