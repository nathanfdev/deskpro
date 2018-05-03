<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

/**
 * Place holder for the next 12 months.
 */
class Next12Months extends AbstractDateRange
{
    /**
     * {@inheritdoc}
     */
    public function getDateRange()
    {
        $date = $this->getDate();
        $now  = $date->format('Y-m-d');

        $date = $this->getDate();
        $date->modify('+12 months');
        $next = $date->format('Y-m-d');

        return ["$now to $next", "$now 00:00:00", "$next 23:59:59"];
    }
}
