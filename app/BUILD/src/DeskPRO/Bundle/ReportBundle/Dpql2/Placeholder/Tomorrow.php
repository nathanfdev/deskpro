<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

/**
 * Placeholder for tomorrow (00:00 - 23:59), based on the current person's time zone.
 */
class Tomorrow extends AbstractDateRange
{
    /**
     * {@inheritdoc}
     */
    protected function getDateRange()
    {
        $date = $this->getDate();
        $date->modify('+1 day');

        $tomorrow = $date->format('Y-m-d');

        return [$tomorrow, "$tomorrow 00:00:00", "$tomorrow 23:59:59"];
    }
}
