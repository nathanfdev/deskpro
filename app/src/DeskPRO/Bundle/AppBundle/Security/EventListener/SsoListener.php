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

namespace DeskPRO\Bundle\AppBundle\Security\EventListener;

use Application\DeskPRO\Auth\AuthenticationManager;
use Application\DeskPRO\Auth\AuthInterfaceSettings;
use DeskPRO\Bundle\AppBundle\Security\Handler\LogoutHandler;
use DeskPRO\Bundle\PortalBundle\EventListener\RedirectProtectionListener;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SsoListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorization_checker;

    /**
     * @var \Application\DeskPRO\Auth\AuthenticationManager
     */
    private $auth_manager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(AuthorizationCheckerInterface $authorization_checker, AuthenticationManager $auth_manager, LoggerInterface $logger)
    {
        $this->authorization_checker = $authorization_checker;
        $this->auth_manager          = $auth_manager;
        $this->logger                = $logger;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if ($this->authorization_checker->isGranted('ROLE_USER')) {
            return;
        }

        // if we need to return a redirect from the auth system, do so now
        if ($res = $this->checkAuthSystemForResponse($this->auth_manager->getSettings(), $event->getRequest())) {
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
    protected function checkAuthSystemForResponse(
        AuthInterfaceSettings $authInterfaceSettings, Request $request
    ) {
        $session = $request->getSession();
        if (
            null !== $session
            && $session->isStarted()
            && $session->has(LogoutHandler::RECENT_LOGOUT)
            && $session->get(LogoutHandler::RECENT_LOGOUT) > 0
        ) {
            $session->remove(LogoutHandler::RECENT_LOGOUT);
            if ($url = $authInterfaceSettings->getLogoutRedirectUrl()) {
                return new RedirectResponse($url);
            }
        }

        if ($sso_result = $this->handleAutomaticSso($authInterfaceSettings)) {
            if ($sso_result->isRedirectRequired()) {
                $return = $request->get('return');
                $session->set('auth_return', $return);

                return new RedirectResponse($sso_result->getRedirectUrl());
            }
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

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onKernelRequest',
        );
    }
}
