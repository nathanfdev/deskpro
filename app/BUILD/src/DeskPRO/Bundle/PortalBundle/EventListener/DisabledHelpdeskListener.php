<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\HttpKernel\SkipLowRequestInterface;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * If the helpdesk is disabled, we send a response back immediately from this request listener.
 */
class DisabledHelpdeskListener implements EventSubscriberInterface, SkipLowRequestInterface
{
    public static $whitelistedRouteNames = [
        'user_context_hash',
        'user_logout',
        'portal_ping',
        'portal_reset_password_process',
        'portal_set_password_process',
        'gregwar_captcha.generate_captcha',
    ];

    /**
     * @var SettingsResolver
     */
    private $resolver;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(BrandStack $brand_stack, SettingsResolver $resolver, LoggerInterface $logger)
    {
        $this->resolver   = $resolver;
        $this->brandStack = $brand_stack;
        $this->logger     = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            // make sure this priority is AFTER the BrandDetectionListener so we capture brand settings
            // AND it also must be AFTER the RouterListener so we can whitelist routes
            KernelEvents::REQUEST => ['onRequest', 31],
        ];
    }

    public function onRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // we only make this decision on master requests. sub requests are never "offline".
            return;
        }

        if ($this->isWhitelisted($event->getRequest())) {
            return;
        }

        $brand         = $this->brandStack->getActive();
        $brandDisabled = $brand->getSetting('core.helpdesk_disabled', false);

        $globallyDisabled = $this->resolver->getGlobalSettings()->get('core.helpdesk_disabled', false);

        if ($globallyDisabled || $brandDisabled) {
            // we can always use brand settings here, because they inherit global in case brand specific is not set
            $event->setResponse(new Response('<!--PORTAL_OFFLINE-->'.$brand->getSetting('core.helpdesk_disabled_message')));
        }
    }

    protected function isWhitelisted(Request $request)
    {
        $routeName = $request->attributes->get('_route');

        return in_array($routeName, self::$whitelistedRouteNames);
    }
}
