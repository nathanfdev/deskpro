<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

class NextHour extends AbstractDateRange
{
    /**
     * {@inheritdoc}
     */
    public function getDateRange()
    {
        $date = $this->getDate();
        $now  = $date->format('Y-m-d H:i:s');

        $date = $this->getDate();
        $date->modify('+1 hours');
        $next = $date->format('Y-m-d H:i:s');

        return ["$now to $next", "$now", "$next"];
    }
}
