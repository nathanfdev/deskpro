<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

class Next12Hours extends AbstractDateRange
{
    /**
     * {@inheritdoc}
     */
    protected function getDateRange()
    {
        $date = $this->getDate();
        $now  = $date->format('Y-m-d');

        $date = $this->getDate();
        $date->modify('+12 hours');
        $next = $date->format('Y-m-d');

        return ["$now to $next", "$now", "$next"];
    }
}
