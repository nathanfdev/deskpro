<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Event\People;

use DeskPRO\Bundle\AppBundle\Notification\Event\AbstractSystemEvent;

/**
 * Class UserChatAgentsOnlineEvent.
 */
class UserChatAgentsOnlineEvent extends AbstractSystemEvent
{
    const EVENT_NAME = 'user_chat.agents_online';

    /** @var array */
    protected $agentIds;

    /**
     * @param array $agentIds
     */
    public function __construct(array $agentIds)
    {
        $this->agentIds = $agentIds;
    }

    /**
     * @return array
     */
    public function getAgentIds()
    {
        return $this->agentIds;
    }

    public function __sleep()
    {
        return ['agentIds'];
    }
}
