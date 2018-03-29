<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class AbstractSystemEvent.
 */
abstract class AbstractSystemEvent extends Event implements SystemEventInterface
{
    const EVENT_NAME = 'abstract.event';

    public function getName()
    {
        return static::EVENT_NAME;
    }
}
