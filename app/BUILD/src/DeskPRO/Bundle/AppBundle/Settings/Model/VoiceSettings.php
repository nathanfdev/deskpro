<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class VoiceSettings.
 */
class VoiceSettings
{
    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $agentVoicemailTimeout;

    /**
     * @return int
     */
    public function getAgentVoicemailTimeout()
    {
        return $this->agentVoicemailTimeout;
    }

    /**
     * @param int $agentVoicemailTimeout
     *
     * @return $this
     */
    public function setAgentVoicemailTimeout($agentVoicemailTimeout)
    {
        $this->agentVoicemailTimeout = $agentVoicemailTimeout;

        return $this;
    }
}
