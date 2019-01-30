<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\CloudEmails;

use JMS\Serializer\Annotation as JMS;
use Application\EmailBundle\Queue\QueueRunMetrics;

class DTOOutgoingJob
{
    /**
     * @JMS\Type("array<string, int>")
     *
     * @var array
     */
    private $metrics = [];

    /**
     * @return array
     */
    public function getMetrics()
    {
        return $this->metrics;
    }

    /**
     * @param array $metrics
     */
    public function setMetrics( $metrics )
    {
        $this->metrics = $metrics;
    }

    public function setQueueRunMetrics(QueueRunMetrics $metrics)
    {
        $this->metrics = [
            "totalSent" => $metrics->getTotalSent(),
            "totalBatches" => $metrics->getTotalBatches()
        ];
    }

}
