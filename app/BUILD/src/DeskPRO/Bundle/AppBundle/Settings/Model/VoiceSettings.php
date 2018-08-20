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
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $groupMissedCallTickets;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $groupMissedCallTicketsTimeout;

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

    /**
     * @return bool
     */
    public function isGroupMissedCallTickets()
    {
        return $this->groupMissedCallTickets;
    }

    /**
     * @param bool $groupMissedCallTickets
     *
     * @return $this
     */
    public function setGroupMissedCallTickets($groupMissedCallTickets)
    {
        $this->groupMissedCallTickets = $groupMissedCallTickets;

        return $this;
    }

    /**
     * @return int
     */
    public function getGroupMissedCallTicketsTimeout()
    {
        return $this->groupMissedCallTicketsTimeout;
    }

    /**
     * @param int $groupMissedCallTicketsTimeout
     *
     * @return $this
     */
    public function setGroupMissedCallTicketsTimeout($groupMissedCallTicketsTimeout)
    {
        $this->groupMissedCallTicketsTimeout = $groupMissedCallTicketsTimeout;

        return $this;
    }
}
