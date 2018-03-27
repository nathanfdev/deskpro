<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestIdResponseListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onResponse', 64],
        ];
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        $request  = $event->getRequest();
        $response = $event->getResponse();

        if ($request->attributes->has('request_id')) {
            $response->headers->add(['X-Request-ID' => $request->attributes->get('request_id')]);
        }

        try {
            $pubref = \DpSys\License::getLicense()->getPublicLicenseRef();
            $response->headers->add(['X-DP-LREF' => $pubref]);
        } catch (\Exception $e) {
        }
    }
}
