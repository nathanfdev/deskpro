<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\HttpCache\EventListener;

use DeskPRO\Bundle\PortalBundle\HttpCache\PortalCacheHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Ensure that cache headers are ONLY sent in the event of a GUEST user-context-hash request.
 */
class PortalUserContextSubscriber implements EventSubscriberInterface
{
    /**
     * @var RequestMatcherInterface
     */
    private $requestMatcher;

    /**
     * @var string
     */
    private $hashHeader;

    /**
     * @var PortalCacheHelper
     */
    private $cache_helper;

    public function __construct(
        RequestMatcherInterface $requestMatcher,
        PortalCacheHelper $cache_helper,
        $hashHeader = 'X-User-Context-Hash'
    ) {
        $this->requestMatcher = $requestMatcher;
        $this->hashHeader     = $hashHeader;
        $this->cache_helper   = $cache_helper;
    }

    /**
     * Turn off cache headers if the hash is NOT a guest hash.
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        // ensure its a user-context-hash request
        if (!$this->requestMatcher->matches($event->getRequest())) {
            return;
        }

        $response = $event->getResponse();

        // ensure the response has the hash
        if (!$response->headers->has($this->hashHeader)) {
            return;
        }

        $hash = $response->headers->get($this->hashHeader);

        if ($this->cache_helper->isGuestHash($hash)) {
            return; // leave the response as is, this is a guest
        }

        // undo the UserContextSubscriber by making it a no-cache response
        $response->setClientTtl(0);
        $response->headers->addCacheControlDirective('no-cache');

        // users with a session will now always ask for the hash, since our proxy wont cache this $response
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', 256], // run this right after the UserContextsubscriber
        ];
    }
}
