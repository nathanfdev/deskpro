<?php

namespace Application\EmailBundle\Queue;

class QueueRunMetrics
{
    /** @var int */
    private $totalSent;

    /** @var int */
    private $totalBatches;

    /**
     * QueueRunMetrics constructor.
     * @param int $totalSent
     * @param int $totalBatches
     */
    public function __construct( $totalSent, $totalBatches )
    {
        $this->totalSent = $totalSent;
        $this->totalBatches = $totalBatches;
    }

    /**
     * @return int
     */
    public function getTotalSent()
    {
        return $this->totalSent;
    }

    /**
     * @return int
     */
    public function getTotalBatches()
    {
        return $this->totalBatches;
    }
}
