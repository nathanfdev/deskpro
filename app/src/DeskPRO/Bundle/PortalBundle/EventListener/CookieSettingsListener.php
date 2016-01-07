<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onResponse',
            KernelEvents::REQUEST  => 'onRequest',
        ];
    }
}
