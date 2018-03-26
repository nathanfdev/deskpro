<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Event\Helpdesk;

use DeskPRO\Bundle\AppBundle\Notification\Event\AbstractSystemEvent;

class RefreshAgentInterfaceEvent extends AbstractSystemEvent
{
    const EVENT_NAME = 'helpdesk.agent.refresh_interface';

    private $who             = null;
    private $message         = null;
    private $isIgnoreAllowed = false;
    private $reasonCode      = null;

    /**
     * RefreshAgentInterfaceEvent constructor.
     *
     * @param null|string $who
     * @param null|string $message
     * @param bool        $isIgnoreAllowed
     * @param null|string $reasonCode
     */
    public function __construct($who = null, $message = null, $isIgnoreAllowed = false, $reasonCode = null)
    {
        $this->who             = $who;
        $this->message         = $message;
        $this->isIgnoreAllowed = $isIgnoreAllowed;
        $this->reasonCode      = $reasonCode;
    }

    /**
     * @return null|string
     */
    public function getWho()
    {
        return $this->who;
    }

    /**
     * @return null|string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return bool
     */
    public function isIgnoreAllowed()
    {
        return $this->isIgnoreAllowed;
    }

    /**
     * @return null|string
     */
    public function getReasonCode()
    {
        return $this->reasonCode;
    }

    /**
     * {@inheritdoc}
     */
    public function __sleep()
    {
        return ['who', 'message'];
    }
}
