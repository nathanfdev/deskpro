<?php

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Setup the cookie settings. If it is a HTTPS request, ensure we set secure cookies.
 */
class CookieSettingsListener implements EventSubscriberInterface
{
    public function onRequest(GetResponseEvent $event)
    {
        if ($event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        if ($request->isSecure()) {
            ini_set('session.cookie_secure', true);
        }
    }

    public function onResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request  = $event->getRequest();
        $response = $event->getResponse();

        if ($request->isSecure()) {
            ini_set('session.cookie_secure', true);

            // convert all cookies on the response into secure cookies
            /** @var \Symfony\Component\HttpFoundation\Cookie $cookie */
            foreach ($response->headers->getCookies() as $cookie) {
                $new_cookie = new Cookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), true, $cookie->isHttpOnly());
                $response->headers->removeCookie($cookie->getName(), $cookie->getPath(), $cookie->getDomain());
                $response->headers->setCookie($new_cookie);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onResponse',
            KernelEvents::REQUEST  => 'onRequest',
        ];
    }
}
