<?php

namespace DeskPRO\Bundle\AppBundle\Webhooks;

class ExecutionStats
{
    /** @var array|string[] */
    private $webhookMatched;

    /** @var array|string[] */
    private $triggerMatched;

    /**
     * @param array | string[] $webhookMatched
     * @param array | string[] $triggerMatched
     */
    public function __construct ( $webhookMatched, $triggerMatched)
    {
        $this->webhookMatched = $webhookMatched;
        $this->triggerMatched = $triggerMatched;
    }

    /**
     * @return array|string[]
     */
    public function getMatchedByWebhook()
    {
        return $this->webhookMatched;
    }

    public function getMatchedByTriggers()
    {
        return $this->triggerMatched;
    }

}
