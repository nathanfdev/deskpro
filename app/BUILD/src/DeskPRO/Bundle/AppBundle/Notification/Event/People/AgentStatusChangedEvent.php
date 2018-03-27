<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Event\People;

use DeskPRO\Bundle\AppBundle\Notification\Event\AbstractLegacyEvent;

class AgentStatusChangedEvent extends AbstractLegacyEvent
{
    const EVENT_NAME = 'agent.update_status';

    /**
     * @var int
     */
    private $personId;

    /**
     * @var bool
     */
    private $online;

    /**
     * AgentStatusChangedEvent constructor.
     *
     * @param $type
     * @param $personId
     * @param $online
     */
    public function __construct($type, $personId, $online)
    {
        parent::__construct($type);
        $this->personId = $personId;
        $this->online   = $online;
    }

    /**
     * @return int
     */
    public function getPersonId()
    {
        return $this->personId;
    }

    /**
     * @return bool
     */
    public function getOnline()
    {
        return $this->online;
    }

    public function __sleep()
    {
        return ['personId', 'online'];
    }
}
