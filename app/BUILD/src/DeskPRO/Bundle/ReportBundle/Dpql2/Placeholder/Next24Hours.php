<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

class Next24Hours extends AbstractDateRange
{
    /**
     * {@inheritdoc}
     */
    protected function getDateRange()
    {
        $date = $this->getDate();
        $now  = $date->format('Y-m-d H:i:s');

        $date = $this->getDate();
        $date->modify('+24 hours');
        $next = $date->format('Y-m-d H:i:s');

        return ["$now to $next", "$now 00:00:00", "$next 23:59:59"];
    }
}
