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

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\HttpKernel\SkipLowRequestInterface;
use DeskPRO\Bundle\AppBundle\Settings\WidgetSettingsResolver;
use DeskPRO\Bundle\PortalBundle\Brand\BrandContainer;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * If the portal is disabled, we send a response back immediately from this request listener.
 */
class DisabledPortalListener implements EventSubscriberInterface, SkipLowRequestInterface
{
    public static $whitelistedChatWidgetRouteNames = [
        'deskpro_portal_api_chat_createnewchat',
        'deskpro_portal_api_chat_regenerateemailvalidationcode',
        'deskpro_portal_api_chat_validateemail',
        'deskpro_portal_api_chat_pollingchat',
        'deskpro_portal_api_chat_sendmessage',
        'deskpro_portal_api_chat_ackmessages',
        'deskpro_portal_api_chat_usertyping',
        'deskpro_portal_api_chat_sendtranscriptinfo',
        'deskpro_portal_api_chat_toggleshouldsendtranscript',
        'deskpro_portal_api_chat_endchat',
        'deskpro_portal_api_chat_reopenchat',
        'deskpro_portal_api_chat_feedback',
        'deskpro_portal_api_chatdepartments_getchatdepartments',
        'deskpro_portal_api_chat_getcustomfields',
        'deskpro_portal_api_people_getonlineagents',
        'deskpro_portal_api_auth_getsession',
        'deskpro_portal_api_widget_getwidgetoptions',
        'portal_api_lang_widget_phrases',
        'portal_api_ticket_new',
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
     * @param BrandStack            $brandStack
     * @param SettingsResolver      $resolver
     * @param LoggerInterface       $logger
     * @param EngineInterface       $portalTpl
     * @param PortalModeStorage     $modeStorage
     * @param TokenStorageInterface $tokenStorage
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
            if ($this->isWhiteListedChatRoute($event->getRequest(), $brand)) {
                return;
            }

            if (strpos($event->getRequest()->getPathInfo(), '/portal/api') === 0) {
                $event->setResponse(new JsonResponse([
                    'code'    => Response::HTTP_FORBIDDEN,
                    'message' => 'The portal has been disabled.',
                ], Response::HTTP_FORBIDDEN));
            } else {
                $event->setResponse($this->portalTpl->renderResponse('Theme:Portal:portal-disabled.html.twig'));
            }
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
     * @param Request              $request
     * @param Brand|BrandContainer $brand
     *
     * @return bool
     */
    protected function isWhiteListedChatRoute(Request $request, BrandContainer $brand)
    {
        if (
            $brand->getSetting(WidgetSettingsResolver::ENABLED_ON_PORTAL)
            && $brand->getSetting(WidgetSettingsResolver::CHAT_ENABLED)
        ) {
            if ($this->isWhitelisted($request, static::$whitelistedChatWidgetRouteNames)) {
                return true;
            }
        }

        return false;
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

    /**
     * @param GetResponseEvent $event
     *
     * @return bool
     */
    private function isFavicon(GetResponseEvent $event)
    {
        return $event->getRequest()->getPathInfo() === '/favicon.ico';
    }
}
