<?php

namespace DeskPRO\Bundle\VoiceBundle\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class WorkerActivityStatus.
 */
class WorkerActivityStatus
{
    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $agentId;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $online;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $voiceEnabled;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $forwardingEnabled;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $busyForVoice;

    /**
     * @return int
     */
    public function getAgentId()
    {
        return $this->agentId;
    }

    /**
     * @param int $agentId
     *
     * @return $this
     */
    public function setAgentId($agentId)
    {
        $this->agentId = $agentId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOnline()
    {
        return $this->online;
    }

    /**
     * @param bool $online
     *
     * @return $this
     */
    public function setOnline($online)
    {
        $this->online = $online;

        return $this;
    }

    /**
     * @return bool
     */
    public function isVoiceEnabled()
    {
        return $this->voiceEnabled;
    }

    /**
     * @param bool $voiceEnabled
     *
     * @return $this
     */
    public function setVoiceEnabled($voiceEnabled)
    {
        $this->voiceEnabled = $voiceEnabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isForwardingEnabled()
    {
        return $this->forwardingEnabled;
    }

    /**
     * @param bool $forwardingEnabled
     *
     * @return $this
     */
    public function setForwardingEnabled($forwardingEnabled)
    {
        $this->forwardingEnabled = $forwardingEnabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBusyForVoice()
    {
        return $this->busyForVoice;
    }

    /**
     * @param bool $busyForVoice
     *
     * @return $this
     */
    public function setBusyForVoice($busyForVoice)
    {
        $this->busyForVoice = $busyForVoice;

        return $this;
    }
}
