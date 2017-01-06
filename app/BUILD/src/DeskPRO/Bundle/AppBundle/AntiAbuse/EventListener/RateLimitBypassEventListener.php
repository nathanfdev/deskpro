<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
