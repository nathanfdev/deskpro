<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\AntiAbuse\EventListener;

use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\AntiAbuseEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Allows to bypass login ratelimit if special header provided.
 */
class RateLimitBypassEventListener implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            AntiAbuse::getEventName(AntiAbuse::ACTION_LOGIN) => 'checkBypass',
        ];
    }

    /**
     * RateLimitBypassEventListener constructor.
     *
     * @param LoggerInterface $logger
     * @param RequestStack    $requestStack
     */
    public function __construct(RequestStack $requestStack, LoggerInterface $logger)
    {
        $this->logger       = $logger;
        $this->requestStack = $requestStack;
    }

    /**
     * @param AntiAbuseEvent $event
     *
     * @throws \Exception
     */
    public function checkBypass(AntiAbuseEvent $event)
    {
        if ($request = $this->requestStack->getCurrentRequest()) {
            $bypass =
                $request->query->get('DP_BYPASS_TOKEN_AUTH', false) ?:
                $request->request->get('DP_BYPASS_TOKEN_AUTH', false) ?:
                $request->headers->get('DP_BYPASS_TOKEN_AUTH', false);

            if ($bypass && defined('DP_BYPASS_TOKEN_AUTH') && DP_BYPASS_TOKEN_AUTH === $bypass) {
                $event->stopPropagation();
            }
        }

        return;
    }
}
