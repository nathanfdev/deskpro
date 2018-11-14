<?php

namespace DeskPRO\Bundle\VoiceBundle\Settings\Model;

/**
 * Class ChatQueueSettings.
 */
class ChatQueueSettings
{
    /**
     * @var int
     */
    private $agentTimeout;

    /**
     * @var int
     */
    private $maxChatsCount;

    /**
     * @var int
     */
    private $defaultQueue;

    /**
     * @return int
     */
    public function getAgentTimeout()
    {
        return $this->agentTimeout;
    }

    /**
     * @param int $agentTimeout
     *
     * @return $this
     */
    public function setAgentTimeout($agentTimeout)
    {
        $this->agentTimeout = $agentTimeout;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxChatsCount()
    {
        return $this->maxChatsCount;
    }

    /**
     * @param int $maxChatsCount
     *
     * @return $this
     */
    public function setMaxChatsCount($maxChatsCount)
    {
        $this->maxChatsCount = $maxChatsCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getDefaultQueue()
    {
        return $this->defaultQueue;
    }

    /**
     * @param int $defaultQueue
     *
     * @return $this
     */
    public function setDefaultQueue($defaultQueue)
    {
        $this->defaultQueue = $defaultQueue;

        return $this;
    }
}
