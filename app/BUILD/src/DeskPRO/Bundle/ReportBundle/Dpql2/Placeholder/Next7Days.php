<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

class Next7Days extends AbstractDateRange
{
    /**
     * {@inheritdoc}
     */
    public function getDateRange()
    {
        $date = $this->getDate();
        $now  = $date->format('Y-m-d');

        $date = $this->getDate();
        $date->modify('+7 days');
        $next = $date->format('Y-m-d');

        return ["$now to $next", "$now 00:00:00", "$next 23:59:59"];
    }
}
