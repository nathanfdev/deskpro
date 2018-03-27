<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Event\People;

use DeskPRO\Bundle\AppBundle\Notification\Event\AbstractSystemEvent;

/**
 * Class UpdateOnlineEvent.
 */
class UpdateOnlineEvent extends AbstractSystemEvent
{
    const EVENT_NAME = 'agents.update_online';

    /** @var array */
    protected $agents_online_status;

    /**
     * @param array $agents_online_status
     */
    public function __construct(array $agents_online_status)
    {
        $this->agents_online_status = $agents_online_status;
    }

    /**
     * @return array
     */
    public function getAgentsOnlineStatus()
    {
        return $this->agents_online_status;
    }

    public function getOnlineAgents()
    {
        return $this->agents_online_status['online'];
    }

    public function getOfflineStatus()
    {
        return $this->agents_online_status['offline'];
    }

    public function __sleep()
    {
        return ['agents_online_status'];
    }
}
