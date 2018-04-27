<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlContextStorage;

class CustomDateRange extends AbstractDateRange
{
    /**
     * @var \DateTime
     */
    private $startDate;

    /**
     * @var \DateTime
     */
    private $endDate;

    /**
     * CustomDateRange constructor.
     *
     * @param DpqlContextStorage $dpqlContextStorage
     * @param \DateTime          $startDate
     * @param \DateTime          $endDate
     */
    public function __construct(DpqlContextStorage $dpqlContextStorage, \DateTime $startDate, \DateTime $endDate)
    {
        parent::__construct($dpqlContextStorage);
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDateRange()
    {
        $startDateValue = $this->startDate->format('Y-m-d');
        $endDateValue   = $this->endDate->format('Y-m-d');

        return ["$startDateValue to $endDateValue", "$startDateValue 00:00:00", "$endDateValue 23:59:59"];
    }
}
