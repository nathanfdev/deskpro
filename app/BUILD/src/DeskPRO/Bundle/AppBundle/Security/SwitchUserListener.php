<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Security;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Based on built in Symfony 2 SwitchUserListener class.
 *
 * Modified to allow:
 * 1. Redirect user on exit back to original impersonation url if one exists
 * 2. Allow user to impersonate different user if they are already impersonating a user
 * 3. Allow to impersonate anon
 * 4. Allow to _exit from impersonation even when not impersonating w/o exception
 */
class SwitchUserListener implements ListenerInterface
{
    /**
     * @var SecurityContextInterface
     */
    private $token_storage;

    /**
     * @var UserProviderInterface
     */
    private $provider;

    /**
     * @var UserCheckerInterface
     */
    private $user_checker;

    /**
     * @var string
     */
    private $provider_key;

    /**
     * @var AccessDecisionManagerInterface
     */
    private $access_decision_manager;

    /**
     * @var string
     */
    private $username_parameter;

    /**
     * @var string
     */
    private $role;

    /**
     * @var null|LoggerInterface
     */
    private $logger;

    /**
     * @var null|EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var bool Disables the URI redirect in case user is already impersonating one user and trying to switch to another
     */
    private $use_override_uri = false;

    /**
     * @param TokenStorageInterface $token_storage
     * @param UserProviderInterface $provider
     * @param UserCheckerInterface  $user_checker
     * @param $provider_key
     * @param AccessDecisionManagerInterface $access_decision_manager
     * @param LoggerInterface|null           $logger
     * @param string                         $username_parameter
     * @param string                         $role
     * @param EventDispatcherInterface|null  $dispatcher
     */
    public function __construct(
        TokenStorageInterface $token_storage,
        UserProviderInterface $provider,
        UserCheckerInterface $user_checker,
        $provider_key,
        AccessDecisionManagerInterface $access_decision_manager,
        LoggerInterface $logger = null,
        $username_parameter = '_switch_user',
        $role = 'ROLE_ALLOWED_TO_SWITCH',
        EventDispatcherInterface $dispatcher = null
    ) {
        if (empty($provider_key)) {
            throw new \InvalidArgumentException('$provider_key must not be empty.');
        }

        $this->token_storage           = $token_storage;
        $this->provider                = $provider;
        $this->user_checker            = $user_checker;
        $this->provider_key            = $provider_key;
        $this->access_decision_manager = $access_decision_manager;
        $this->username_parameter      = $username_parameter;
        $this->role                    = $role;
        $this->logger                  = $logger;
        $this->dispatcher              = $dispatcher;
    }

    /**
     * Handles the switch to another user.
     *
     * @param GetResponseEvent $event A GetResponseEvent instance
     *
     * @throws \LogicException if switching to a user failed
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->get($this->username_parameter)) {
            return;
        }

        if ('_exit' === $request->get($this->username_parameter)) {
            if ($original_token = $this->attemptExitUser($request)) {
                $this->token_storage->setToken($original_token);
            }
        } else {
            try {
                $this->token_storage->setToken($this->attemptSwitchUser($request));
            } catch (AuthenticationException $e) {
                throw new \LogicException(sprintf('Switch User failed: "%s"', $e->getMessage()));
            }
        }

        $session = $request->getSession();

        $request->query->remove($this->username_parameter);

        $override_uri = $session->get('onSwitchURI', null);
        if ($request->get('returnTo')) {
            $session->set('onSwitchURI', $request->get('returnTo'));
            $request->query->remove('returnTo');
        } else {
            $session->remove('onSwitchURI');
        }

        $request->server->set('QUERY_STRING', http_build_query($request->query->all()));

        $response = new RedirectResponse(
            $this->use_override_uri && $override_uri ? $override_uri : $request->getUri(), 302);

        $event->setResponse($response);
    }

    /**
     * Attempts to switch to another user.
     *
     * @param Request $request A Request instance
     *
     * @throws \LogicException
     * @throws AccessDeniedException
     *
     * @return TokenInterface|null The new TokenInterface if successfully switched, null otherwise
     */
    private function attemptSwitchUser(Request $request)
    {
        $token          = $this->token_storage->getToken();
        $original_token = $this->getOriginalToken($token);

        if (false !== $original_token) {
            if ($token->getUsername() === $request->get($this->username_parameter)) {
                return $token;
            } else {
                // User is impersonating someone, they are trying to switch directly to another user,
                // make sure original user has access
                if (false === $this->access_decision_manager->decide($original_token, [$this->role])) {
                    throw new AccessDeniedException();
                }

                $this->use_override_uri = false;
            }
        } elseif (false === $this->access_decision_manager->decide($token, [$this->role])) {
            throw new AccessDeniedException();
        }

        $username = $request->get($this->username_parameter);

        if (null !== $this->logger) {
            $this->logger->info(sprintf('Attempt to switch to user "%s"', $username));
        }

        try {
            $user = $this->provider->loadUserByUsername($username);
        } catch (UsernameNotFoundException $e) {
            $back_token = $original_token ?: $this->token_storage->getToken();

            return new UsernamePasswordToken(
                'anon', 'anon', $this->provider_key, [new SwitchUserRole('ROLE_PREVIOUS_ADMIN', $back_token)]);
        }

        $this->user_checker->checkPostAuth($user);

        $roles = $user->getRoles();

        // If there is an original token, only let them switch back to that user.
        if ($original_token) {
            $roles[] = new SwitchUserRole('ROLE_PREVIOUS_ADMIN', $original_token);
        } else {
            $roles[] = new SwitchUserRole('ROLE_PREVIOUS_ADMIN', $this->token_storage->getToken());
        }

        $token = new UsernamePasswordToken($user, $user->getPassword(), $this->provider_key, $roles);

        if (null !== $this->dispatcher) {
            $switchEvent = new SwitchUserEvent($request, $token->getUser());
            $this->dispatcher->dispatch(SecurityEvents::SWITCH_USER, $switchEvent);
        }

        return $token;
    }

    /**
     * Attempts to exit from an already switched user.
     *
     * @param Request $request A Request instance
     *
     * @return TokenInterface The original TokenInterface instance
     */
    private function attemptExitUser(Request $request)
    {
        $original = $this->getOriginalToken($this->token_storage->getToken());

        if (null !== $this->dispatcher && $original) {
            $switchEvent = new SwitchUserEvent($request, $original->getUser());
            $this->dispatcher->dispatch(SecurityEvents::SWITCH_USER, $switchEvent);
        }

        return $original;
    }

    /**
     * Gets the original Token from a switched one.
     *
     * @param TokenInterface $token A switched TokenInterface instance
     *
     * @return TokenInterface|false The original TokenInterface instance, false if the current TokenInterface is not switched
     */
    private function getOriginalToken(TokenInterface $token)
    {
        foreach ($token->getRoles() as $role) {
            if ($role instanceof SwitchUserRole) {
                return $role->getSource();
            }
        }

        return false;
    }
}
