<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\Entity\TicketTrigger;

abstract class AbstractAction implements ActionInterface
{
    /**
     * @var \Application\DeskPRO\Entity\TicketTrigger
     */
    protected $trigger;

    /**
     * @var array
     */
    protected $metadata = [];

    /**
     * @param \Application\DeskPRO\Entity\TicketTrigger $trigger
     */
    public function setTrigger(TicketTrigger $trigger)
    {
        $this->trigger = $trigger;
    }

    /**
     * {@inheritdoc}
     */
    public function setMetaData(array $metadata)
    {
        if ($this->metadata) {
            $metadata = array_merge($this->metadata, $metadata);
        }

        $this->metadata = $metadata;
    }

    /**
     * @param string $k
     * @param mixed  $v
     */
    public function addMetaData($k, $v)
    {
        $this->metadata[$k] = $v;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaData($k = null, $default = null)
    {
        if ($k === null) {
            return $this->metadata;
        }

        return isset($this->metadata[$k]) ? $this->metadata[$k] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function doPrepend()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getActionName()
    {
        return get_class($this);
    }
}
