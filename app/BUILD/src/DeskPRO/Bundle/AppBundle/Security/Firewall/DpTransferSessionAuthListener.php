<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Security\Firewall;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Session;
use DeskPRO\Bundle\AppBundle\Security\DpTransferSessionAuthToken;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;

/**
 * Handles a session transfer. If you are not logged into portal but you are logged into admin area, for ex,
 * it will detect that (the detection actually occurs in DpAuthListener) and then it will do what it needs
 * to do to transfer the session and authentcate you in the portal.
 */
class DpTransferSessionAuthListener implements ListenerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AuthenticationManagerInterface
     */
    private $authenticationManager;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var SessionAuthenticationStrategyInterface
     */
    private $sessionStrategy;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * DpTransferSessionAuthListener constructor.
     *
     * Note: These are the same constructor args used by AbstractAuthenticationListener.
     * This is just so we don't need to fuss around with a custom SecurityFactoryInterface, we can use the default.
     *
     * And we don't simply extend AbstractAuthenticationListener because we need a custom handle() (which is marked final in that class).
     * We need a custom handle() because we want to transparently transfer the session without causing an annoying redirect.
     *
     * @param TokenStorageInterface                  $tokenStorage
     * @param AuthenticationManagerInterface         $authenticationManager
     * @param SessionAuthenticationStrategyInterface $sessionStrategy
     * @param HttpUtils                              $httpUtils
     * @param                                        $providerKey
     * @param AuthenticationSuccessHandlerInterface  $successHandler
     * @param AuthenticationFailureHandlerInterface  $failureHandler
     * @param array                                  $options
     * @param LoggerInterface|null                   $logger
     * @param EventDispatcherInterface|null          $dispatcher
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        SessionAuthenticationStrategyInterface $sessionStrategy,
        HttpUtils $httpUtils,
        $providerKey,
        AuthenticationSuccessHandlerInterface $successHandler,
        AuthenticationFailureHandlerInterface $failureHandler,
        array $options = [],
        LoggerInterface $logger = null,
        EventDispatcherInterface $dispatcher = null
    ) {
        $this->tokenStorage          = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->sessionStrategy       = $sessionStrategy;
        $this->logger                = $logger;
        $this->dispatcher            = $dispatcher;
    }

    /**
     * Carries an agent session over to portal.
     *
     * @param GetResponseEvent $event A GetResponseEvent instance
     */
    public function handle(GetResponseEvent $event)
    {
        if (null !== $this->tokenStorage->getToken()) {
            return;
        }

        $request = $event->getRequest();

        if (!$this->requiresAuthentication($request)) {
            return;
        }

        try {
            $token = $this->attemptAuthentication($request);
        } catch (\Exception $e) {
            $token = null;
        }

        if ($token) {
            $this->tokenStorage->setToken($token);

            if (null !== $this->dispatcher) {
                $loginEvent = new InteractiveLoginEvent($request, $token);
                $this->dispatcher->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $loginEvent);
            }

            if ($request->hasSession() && $request->getSession()->isStarted()) {
                $this->sessionStrategy->onAuthentication($request, $token);
            }

            if (null !== $this->logger) {
                $this->logger->debug('Populated the token storage with a DpTransferSessionAuthToken');
            }
        }
    }

    protected function requiresAuthentication(Request $request)
    {
        if ($request->attributes->get('_route') == 'portal_agent_login') {
            // impossible to run if user is trying to impersonate
            return false;
        }

        // if there is no session, we can't be impersonating
        if (!$request->hasPreviousSession()) {
            return false;
        }

        if ($request->getSession()->get('is_impersonating', false)) {
            return false;
        }

        if ($this->checkAgentInterfaceAuthNeedsTransfer($request)) {
            return true;
        }

        return false;
    }

    /**
     * Returns null if nothing is interesting, but will give you a session ID from the "other side"
     * if one exists and we are not logged into the portal yet.
     *
     * @param Request $request
     *
     * @return mixed the session ID from the agent/admin/reporting side or FALSE
     */
    protected function checkAgentInterfaceAuthNeedsTransfer(Request $request)
    {
        // not logged in to portal
        foreach (['dpsid-agent', 'dpsid-admin'] as $cookie) {
            if ($sid = $request->cookies->get($cookie)) {
                if (strpos($sid, '-') === false) {
                    continue;
                }

                $session_id    = Session::getIdFromCode($sid);
                $agent_session = App::getDb()->fetchAssoc(
                    '
                    SELECT person_id, auth
                    FROM sessions
                    WHERE id = ?
                ',
                    [
                        $session_id,
                    ]
                );

                list(, $auth) = explode('-', $sid);
                if ($agent_session && $agent_session['auth'] == $auth && $agent_session['person_id']) {
                    if ($person = App::getEntityRepository('DeskPRO:Person')->find($agent_session['person_id'])) {
                        if (
                            // if the session isnt started, or if it is and we dont have a portal logged in user
                            // NOTE: v. important to be careful to not start the session here
                            !$request->getSession()->isStarted()
                            || ($request->getSession()->isStarted() && !$request->getSession()->get('auth_person_id'))
                        ) {
                            return $sid;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param Request $request
     *
     * @return DpTransferSessionAuthToken|null|TokenInterface
     */
    protected function attemptAuthentication(Request $request)
    {
        $token = null;

        if ($sessionId = $this->checkAgentInterfaceAuthNeedsTransfer($request)) {
            // transfer a login from agent/admin/reporting to portal
            $token = new DpTransferSessionAuthToken(null, $sessionId);
        }

        $r = $this->authenticationManager->authenticate($token);

        return $r;
    }
}
