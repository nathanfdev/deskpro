<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Event;

/**
 * Class AbstractSystemEvent.
 */
abstract class AbstractLegacyEvent extends AbstractSystemEvent implements SystemEventInterface
{
    /**
     * @var string
     */
    protected $eventType;

    public function __construct($eventType)
    {
        $this->eventType = $eventType;
    }

    public function getEventType()
    {
        return $this->eventType;
    }

    public function __sleep()
    {
        return ['eventType'];
    }
}
