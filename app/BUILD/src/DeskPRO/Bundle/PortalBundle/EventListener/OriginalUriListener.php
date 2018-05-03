<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Stick the master URI in the request attributes so it propogates down to ESI calls.
 */
class OriginalUriListener implements EventSubscriberInterface
{
    const ATTR_NAME = '_dp_orig_url';

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // only run this on the master request - we only detect once per request.
            return;
        }

        $request = $event->getRequest();

        if (!$request->attributes->has(self::ATTR_NAME)) {
            $url = $request->getUri();
            $request->attributes->set(self::ATTR_NAME, $url);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            // high priority, must be called BEFORE RouterListener (which is 32)
            KernelEvents::REQUEST => ['onKernelRequest', 33],
        ];
    }
}
