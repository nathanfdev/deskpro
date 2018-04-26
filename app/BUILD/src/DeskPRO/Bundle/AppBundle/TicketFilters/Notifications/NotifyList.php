<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Notifications;

class NotifyList
{
    private $agentIdToTypes = [];

    /**
     * @param int    $agentId
     * @param string $type    The type (type constant from AgentSubscriptionSet)
     */
    public function addAgent($agentId, $type)
    {
        if (!isset($this->agentIdToTypes[$agentId])) {
            $this->agentIdToTypes[$agentId] = [];
        }

        $this->agentIdToTypes[$agentId][] = $type;
    }

    /**
     * @return int[]
     */
    public function getAgentIds()
    {
        return array_keys($this->agentIdToTypes);
    }

    /**
     * @param int $agentId
     *
     * @return string[]
     */
    public function getAgentTypes($agentId)
    {
        return isset($this->agentIdToTypes[$agentId])
            ? $this->agentIdToTypes[$agentId]
            : [];
    }
}
