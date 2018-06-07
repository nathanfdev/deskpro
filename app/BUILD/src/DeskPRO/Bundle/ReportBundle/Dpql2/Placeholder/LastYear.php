<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

/**
 * Place holder for the previous year based on the current person's time zone.
 */
class LastYear extends AbstractDateRange
{
    /**
     * {@inheritdoc}
     */
    public function getDateRange()
    {
        $date        = $this->getDate();
        $year        = $date->format('Y') - 1;
        $beforeStart = new \DateTime("$year-01-01 23:59:59");
        $beforeStart->modify('-1 day');

        return [$year, "$year-01-01 00:00:00", "$year-12-31 23:59:59", $beforeStart->format('Y-m-d H:i:s')];
    }
}
