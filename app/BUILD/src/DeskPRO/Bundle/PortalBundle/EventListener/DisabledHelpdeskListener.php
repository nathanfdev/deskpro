<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\HttpKernel\SkipLowRequestInterface;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
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
        'goto',
        'jira_webhook_handle',
        'api_channel_facebook_incoming',
        'api_channel_incoming_sms_twilio',
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
