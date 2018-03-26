<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Sets an attribute on the request when this is considered a "low level" request.
 * This is read from other listeners to prevent them doing unecessary work.
 */
class RequestTypeClassifierListener implements EventSubscriberInterface
{
    /**
     * A 'low' request is a request that isn't a normal Portal page request. For
     * example, serving a favicon or tracking a pageload via the hit tracker.
     */
    const LOW_REQUEST_ATTR = '_dp_is_low';

    /**
     * An API request is any portal API request (e.g. used by chat/widget). Internal
     * proxy commands are also considered an 'api' request.
     */
    const API_REQUEST_ATTR = '_dp_is_portal_api';

    public function onKernelPreRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has(self::LOW_REQUEST_ATTR)) {
            if (
                preg_match('#^(dp/|favicon\.ico|sitemap\.xml|robots\.txt)#', ltrim($request->getPathInfo(), '/'))
                || preg_match('#^/[a-z]{2}(?:_[A-Z]{2})?/dp/#', $request->getPathInfo())
                || preg_match('#^/app/run/test_ping.html#', $request->getPathInfo())
                || preg_match('#^/generate-captcha/#', $request->getPathInfo())
            ) {
                $request->attributes->set(self::LOW_REQUEST_ATTR, true);
            }
        }

        if (!$request->attributes->has(self::API_REQUEST_ATTR)) {
            if (preg_match('#^([a-z]{2}(?:_[A-Z]{2})?/)?(portal/api/|_wdt/|_proxy|\?tag_options)#', ltrim($request->getPathInfo(), '/'))) {
                $request->attributes->set(self::API_REQUEST_ATTR, true);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            // high priority, called before everything
            KernelEvents::REQUEST => ['onKernelPreRequest', 5000],
        ];
    }
}
