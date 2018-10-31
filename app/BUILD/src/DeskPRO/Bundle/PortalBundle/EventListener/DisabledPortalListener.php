<?php

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\HttpKernel\SkipLowRequestInterface;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
    private $brandStack;

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
     * @var EngineInterface
     */
    private $portalTpl;

    /**
     * Constructor.
     *
     * @param \DeskPRO\Bundle\BrandBundle\Brand\BrandStack $brandStack
     * @param SettingsResolver                             $resolver
     * @param LoggerInterface                              $logger
     * @param EngineInterface                              $portalTpl
     * @param PortalModeStorage                            $modeStorage
     * @param TokenStorageInterface                        $tokenStorage
     */
    public function __construct(
        BrandStack            $brandStack,
        SettingsResolver      $resolver,
        LoggerInterface       $logger,
        EngineInterface       $portalTpl,
        PortalModeStorage     $modeStorage,
        TokenStorageInterface $tokenStorage
    ) {
        $this->resolver     = $resolver;
        $this->brandStack   = $brandStack;
        $this->logger       = $logger;
        $this->portalTpl    = $portalTpl;
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
            || $this->isFavicon($event)
            || $this->isPortalApi($event)
            || $this->isFocusWindow($event)
            || $this->isProxy($event)
        ) {
            // we only make this decision on master requests. sub requests are never "offline".
            // whitlisted routes obviously should pass
            // also preview mode does not disable portal
            // and at last - admin could use portal api always.
            return;
        }

        // we can always use brand settings here, because they inherit global in case brand specific is not set
        $brand              = $this->brandStack->getActive();
        $brandPortalEnabled = (bool) $brand->getSetting('core.iface_portal', true);

        if (!$brandPortalEnabled) {
            $event->setResponse($this->portalTpl->renderResponse('Theme:Portal:portal-disabled.html.twig'));
        }
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    protected function isWhitelisted(Request $request, $routes = null)
    {
        if (!$routes) {
            $routes = DisabledHelpdeskListener::$whitelistedRouteNames;
        }
        $routeName = $request->attributes->get('_route');

        return in_array($routeName, $routes);
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
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return false;
        }

        $user = $token->getUser();

        return
            $user instanceof Person
            && $user->isAdmin()
            && strpos($event->getRequest()->getPathInfo(), 'portal/api') !== false;
    }

    /**
     * @param GetResponseEvent $event
     *
     * @return bool
     */
    private function isFavicon(GetResponseEvent $event)
    {
        return $event->getRequest()->getPathInfo() === '/favicon.ico';
    }

    /**
     * @param GetResponseEvent $event
     *
     * @return bool
     */
    private function isPortalApi(GetResponseEvent $event)
    {
        $request   = $event->getRequest();
        $routeName = $request->attributes->get('_route');

        return $routeName && (strpos($routeName, 'portal_api_') === 0 || strpos($routeName, 'deskpro_portal_api_') === 0);
    }

    /**
     * @param GetResponseEvent $event
     *
     * @return bool
     */
    private function isFocusWindow(GetResponseEvent $event)
    {
        $request   = $event->getRequest();
        $routeName = $request->attributes->get('_route');

        if (!$routeName) {
            return false;
        }

        $whitelistedRoutes = [
            'portal_new_ticket',
            'portal_thanks',
            'portal_thanks_verify',
            'portal_login',
            'portal_validation',
            'portal_search_similar',
            'portal_reset_password',
            'saved_form_auto_submit',
            'dp_pagehit',
        ];

        return $this->modeStorage->getMode()
            && $this->modeStorage->getMode()->isFocusWindow()
            && in_array($routeName, $whitelistedRoutes);
    }

    /**
     * @param GetResponseEvent $event
     *
     * @return bool
     */
    private function isProxy(GetResponseEvent $event)
    {
        return $event->getRequest()->getPathInfo() === '/_proxy';
    }
}
