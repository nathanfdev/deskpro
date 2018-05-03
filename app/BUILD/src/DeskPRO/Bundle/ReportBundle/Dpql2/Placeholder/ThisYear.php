<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

/**
 * Placeholder for the current year (first to last day), based on the current person's time zone.
 */
class ThisYear extends AbstractDateRange
{
    /**
     * {@inheritdoc}
     */
    public function getDateRange()
    {
        $year = $this->getDate()->format('Y');

        return [$year, "$year-01-01 00:00:00", "$year-12-31 23:59:59"];
    }
}
