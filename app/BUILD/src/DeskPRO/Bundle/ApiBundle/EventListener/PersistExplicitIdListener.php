<?php

namespace DeskPRO\Bundle\ApiBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class PersistExplicitIdListener.
 */
class PersistExplicitIdListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest'],
        ];
    }

    /**
     * @internal
     *
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if ($entityId = $request->request->get('with_entity_id')) {
            $request->request->remove('with_entity_id');
            $request->attributes->set('with_entity_id', $entityId);
        }
    }
}
