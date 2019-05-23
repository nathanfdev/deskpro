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
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $agentDefaultDepartment;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $agentDefaultBrand;

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
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $forwardingMachineDetection;

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
     * @return int
     */
    public function getAgentDefaultDepartment()
    {
        return $this->agentDefaultDepartment;
    }

    /**
     * @param int $agentDefaultDepartment
     *
     * @return $this
     */
    public function setAgentDefaultDepartment($agentDefaultDepartment)
    {
        $this->agentDefaultDepartment = $agentDefaultDepartment;

        return $this;
    }

    /**
     * @return int
     */
    public function getAgentDefaultBrand()
    {
        return $this->agentDefaultBrand;
    }

    /**
     * @param int $agentDefaultBrand
     *
     * @return $this
     */
    public function setAgentDefaultBrand($agentDefaultBrand)
    {
        $this->agentDefaultBrand = $agentDefaultBrand;

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

    /**
     * @return bool
     */
    public function isForwardingMachineDetection()
    {
        return $this->forwardingMachineDetection;
    }

    /**
     * @param bool $forwardingMachineDetection
     *
     * @return $this
     */
    public function setForwardingMachineDetection($forwardingMachineDetection)
    {
        $this->forwardingMachineDetection = $forwardingMachineDetection;

        return $this;
    }
}
