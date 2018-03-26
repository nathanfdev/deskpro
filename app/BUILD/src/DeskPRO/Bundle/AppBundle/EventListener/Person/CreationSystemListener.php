<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\EventListener\Person;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Person\Events\PersonCreateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Responsible for making sure the creation_system is set on a person correctly for every NEW person.
 *
 * Current implementation is that it is generated elsewhere and passed with the context
 */
class CreationSystemListener implements EventSubscriberInterface
{
    public function onPreCreate(PersonCreateEvent $event)
    {
        $event->getPerson()->creation_system = $event->getContext()->getCreationSystem();
    }

    public static function getSubscribedEvents()
    {
        return [
            Person::EVENT_PRE_CREATE => 'onPreCreate',
        ];
    }
}
