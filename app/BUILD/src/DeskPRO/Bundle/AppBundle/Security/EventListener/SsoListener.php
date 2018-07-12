<?php

namespace DeskPRO\Bundle\AppBundle\Security\EventListener;

use Application\DeskPRO\Auth\AuthInterfaceSettings;
use DeskPRO\Bundle\AppBundle\Security\Handler\LogoutHandler;
use DeskPRO\Bundle\PortalBundle\EventListener\RedirectProtectionListener;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class SsoListener.
 */
class SsoListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param ContainerInterface            $container
     * @param LoggerInterface               $logger
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, ContainerInterface $container, LoggerInterface $logger)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->container            = $container;
        $this->logger               = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        try {
            if ($this->authorizationChecker->isGranted('ROLE_USER')) {
                return;
            }
        } catch (AuthenticationException $e) {
        }

        $request  = $event->getRequest();
        $pathInfo = $request->getPathInfo();
        if ($request->get('disable_sso')) {
            return;
        }
        if (preg_match('#^/api/#i', $pathInfo) || preg_match('#^/portal/api/#i', $pathInfo)) {
            return;
        }

        // if we need to return a redirect from the auth system, do so now
        $res = $this->checkAuthSystemForResponse($this->container->get('dp_auth_settings')->getUserInterfaceSettings(), $event->getRequest());
        if ($res) {
            $this->logger->info('Automatic SSO is detected, redirecting to '.$res->getTargetUrl());
            $res->headers->set(RedirectProtectionListener::ALLOW_REDIRECT_OFFSITE_HEADER, 1);
            $event->setResponse($res);
        }
    }

    /**
     * Returns a RedirectResponse if SSO says it needs to redirect.
     *
     * @param AuthInterfaceSettings $authInterfaceSettings
     * @param Request               $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function checkAuthSystemForResponse(AuthInterfaceSettings $authInterfaceSettings, Request $request)
    {
        $session = $request->getSession();
        if (
            null !== $session
            && $session->isStarted()
            && $session->has(LogoutHandler::RECENT_LOGOUT)
            && $session->get(LogoutHandler::RECENT_LOGOUT) > 0
        ) {
            $session->remove(LogoutHandler::RECENT_LOGOUT);

            $url = $authInterfaceSettings->getLogoutRedirectUrl();
            if ($url) {
                return new RedirectResponse($url);
            } else {
                return;
            }
        }

        $returnUrl = $request->get('return') ?: $request->getUri();
        if ($returnUrl) {
            $session->set('_security.portal.target_path', $returnUrl);
        }

        $ssoResult = $this->handleAutomaticSso($authInterfaceSettings);
        if ($ssoResult && $ssoResult->isRedirectRequired()) {
            return new RedirectResponse($ssoResult->getRedirectUrl());
        }
    }

    /**
     * @param AuthInterfaceSettings $authInterfaceSettings
     *
     * @return null|\Orb\Auth\Result an auth result is returned if the sso redirect is enabled
     */
    protected function handleAutomaticSso(AuthInterfaceSettings $authInterfaceSettings)
    {
        if ($authInterfaceSettings->isAutoSsoEnabled()) {
            return $authInterfaceSettings->getSsoAuthAdapter()->authenticate();
        }

        return;
    }
}
