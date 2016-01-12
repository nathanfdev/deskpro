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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\PortalBundle\EventListener;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Helper\IsLowLevelRequestHelper;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Twig\Environment;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * If the portal is disabled, we send a response back immediately from this request listener.
 */
class DisabledPortalListener implements EventSubscriberInterface
{
    /**
     * @var SettingsResolver
     */
    private $resolver;

    /**
     * @var BrandStack
     */
    private $brand_stack;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Environment
     */
    private $portal_twig;

    public function __construct(BrandStack $brand_stack, SettingsResolver $resolver, LoggerInterface $logger, TwigEngine $portal_twig)
    {
        $this->resolver    = $resolver;
        $this->brand_stack = $brand_stack;
        $this->logger      = $logger;
        $this->portal_twig = $portal_twig;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            // make sure this priority is AFTER the BrandDetectionListener so we capture brand settings
            // AND it also must be AFTER the RouterListener so we can whitelist routes
            KernelEvents::REQUEST => ['onRequest', 0],
        ];
    }

    public function onRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // we only make this decision on master requests. sub requests are never "offline".
            return;
        }

        if (IsLowLevelRequestHelper::check($event->getRequest())) {
            // dont run on low level
            return;
        }

        if ($this->isWhitelisted($event->getRequest())) {
            return;
        }

        $brand                = $this->brand_stack->getActive();
        $brand_portal_enabled = (bool) $brand->getSetting('user.portal_enabled', true);

        if (!$brand_portal_enabled) {
            // we can always use brand settings here, because they inherit global in case brand specific is not set
            $event->setResponse($this->portal_twig->renderResponse('Theme:Portal:disabled.html.twig'));
        }
    }

    protected function isWhitelisted(Request $request)
    {
        $route_name = $request->attributes->get('_route');

        return in_array($route_name, DisabledHelpdeskListener::$whitelisted_route_names);
    }
}
