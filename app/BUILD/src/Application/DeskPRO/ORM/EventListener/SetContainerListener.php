<?php

/**
 * DeskPRO.
 *
 * @category ORM
 */

namespace Application\DeskPRO\ORM\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

/**
 * This listener automatically sets the container once an ORM entity has been laoded.
 */
class SetContainerListener implements EventSubscriber
{
    /** @var \Symfony\Component\DependencyInjection\Container */
    protected $container;

    public function __construct(\Symfony\Component\DependencyInjection\Container $container)
    {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return [Events::postLoad];
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Entity) {
            if (!$entity->hasContainer()) {
                $entity->setContainer($container);
            }
        }
    }
}
