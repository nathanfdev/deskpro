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
namespace DeskPRO\Bundle\AppBundle\Security;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
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
 * 2. Allow user to impersonate different user if they are already impersonating a user.
 */
class SwitchUserListener implements ListenerInterface
{
    private $securityContext;
    private $provider;
    private $userChecker;
    private $providerKey;
    private $accessDecisionManager;
    private $usernameParameter;
    private $role;
    private $logger;
    private $dispatcher;

    // Used to disable the URI redirect in case user is already impersonating one user and trying to switch to another.
    private $useOverrideUri;

    /**
     * Constructor.
     */
    public function __construct(SecurityContextInterface $securityContext, UserProviderInterface $provider, UserCheckerInterface $userChecker, $providerKey, AccessDecisionManagerInterface $accessDecisionManager, LoggerInterface $logger = null, $usernameParameter = '_switch_user', $role = 'ROLE_ALLOWED_TO_SWITCH', EventDispatcherInterface $dispatcher = null)
    {
        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->securityContext       = $securityContext;
        $this->provider              = $provider;
        $this->userChecker           = $userChecker;
        $this->providerKey           = $providerKey;
        $this->accessDecisionManager = $accessDecisionManager;
        $this->usernameParameter     = $usernameParameter;
        $this->role                  = $role;
        $this->logger                = $logger;
        $this->dispatcher            = $dispatcher;
        $this->useOverrideUri        = true;
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

        if (!$request->get($this->usernameParameter)) {
            return;
        }

        if ('_exit' === $request->get($this->usernameParameter)) {
            $this->securityContext->setToken($this->attemptExitUser($request));
        } else {
            try {
                $this->securityContext->setToken($this->attemptSwitchUser($request));
            } catch (AuthenticationException $e) {
                throw new \LogicException(sprintf('Switch User failed: "%s"', $e->getMessage()));
            }
        }

        $session = $request->getSession();

        $request->query->remove($this->usernameParameter);

        $overrideUri = $session->get('onSwitchURI', null);
        if ($request->get('returnTo')) {
            $session->set('onSwitchURI', $request->get('returnTo'));
            $request->query->remove('returnTo');
        } else {
            $session->remove('onSwitchURI');
        }

        $request->server->set('QUERY_STRING', http_build_query($request->query->all()));

        $response = new RedirectResponse($this->useOverrideUri && $overrideUri ? $overrideUri : $request->getUri(), 302);

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
        $token         = $this->securityContext->getToken();
        $originalToken = $this->getOriginalToken($token);

        if (false !== $originalToken) {
            if ($token->getUsername() === $request->get($this->usernameParameter)) {
                return $token;
            } else {
                // User is impersonating someone, they are trying to switch directly to another user, make sure original user has access.
                if (false === $this->accessDecisionManager->decide($originalToken, [$this->role])) {
                    throw new AccessDeniedException();
                }

                $this->useOverrideUri = false;
            }
        } elseif (false === $this->accessDecisionManager->decide($token, [$this->role])) {
            throw new AccessDeniedException();
        }

        $username = $request->get($this->usernameParameter);

        if (null !== $this->logger) {
            $this->logger->info(sprintf('Attempt to switch to user "%s"', $username));
        }

        $user = $this->provider->loadUserByUsername($username);

        if (!$user) {
            return $originalToken;
        }

        $this->userChecker->checkPostAuth($user);

        $roles = $user->getRoles();

        // If there is an original token, only let them switch back to that user.
        if ($originalToken) {
            $roles[] = new SwitchUserRole('ROLE_PREVIOUS_ADMIN', $originalToken);
        } else {
            $roles[] = new SwitchUserRole('ROLE_PREVIOUS_ADMIN', $this->securityContext->getToken());
        }

        $token = new UsernamePasswordToken($user, $user->getPassword(), $this->providerKey, $roles);

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
     * @throws AuthenticationCredentialsNotFoundException
     *
     * @return TokenInterface The original TokenInterface instance
     */
    private function attemptExitUser(Request $request)
    {
        if (false === $original = $this->getOriginalToken($this->securityContext->getToken())) {
            throw new AuthenticationCredentialsNotFoundException('Could not find original Token object.');
        }

        if (null !== $this->dispatcher) {
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
