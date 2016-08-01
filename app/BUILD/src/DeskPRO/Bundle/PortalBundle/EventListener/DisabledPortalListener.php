<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\HttpKernel\SkipLowRequestInterface;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use DeskPRO\Bundle\PortalBundle\Twig\Environment;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * If the portal is disabled, we send a response back immediately from this request listener.
 */
class DisabledPortalListener implements EventSubscriberInterface, SkipLowRequestInterface
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
     * @var PortalModeStorage
     */
    private $modeStorage;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var Environment
     */
    private $portal_tpl;

    public function __construct(
        BrandStack $brand_stack,
        SettingsResolver $resolver,
        LoggerInterface $logger,
        EngineInterface $portal_tpl,
        PortalModeStorage $modeStorage,
        TokenStorageInterface $tokenStorage
    ) {
        $this->resolver     = $resolver;
        $this->brand_stack  = $brand_stack;
        $this->logger       = $logger;
        $this->portal_tpl   = $portal_tpl;
        $this->modeStorage  = $modeStorage;
        $this->tokenStorage = $tokenStorage;
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

    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (
            !$event->isMasterRequest()
            || $this->isWhitelisted($event->getRequest())
            || $this->isAdminPreview()
            || $this->isAdminPreviewApiCall($event)
        ) {
            // we only make this decision on master requests. sub requests are never "offline".
            // whitlisted routes obviously should pass
            // also preview mode does not disable portal
            // and at last - admin could use portal api always.
            return;
        }

        $brand                = $this->brand_stack->getActive();
        $brand_portal_enabled = (bool) $brand->getSetting('core.iface_portal', true);

        if (!$brand_portal_enabled) {
            // we can always use brand settings here, because they inherit global in case brand specific is not set
            $event->setResponse($this->portal_tpl->renderResponse('Theme:Portal:portal-disabled.html.twig'));
        }
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    protected function isWhitelisted(Request $request)
    {
        $route_name = $request->attributes->get('_route');

        return in_array($route_name, DisabledHelpdeskListener::$whitelisted_route_names);
    }

    /**
     * @return bool
     */
    private function isAdminPreview()
    {
        return $this->modeStorage->getMode() && $this->modeStorage->getMode()->isAdminPreview();
    }

    /**
     * @param GetResponseEvent $event
     *
     * @return bool
     */
    private function isAdminPreviewApiCall(GetResponseEvent $event)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return
            $user instanceof Person
            && $user->isAdmin()
            && strpos($event->getRequest()->getPathInfo(), 'portal/api') !== false;
    }
}
