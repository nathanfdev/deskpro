<?php

namespace DeskPRO\Bundle\AppBundle\Security\Authentication\Provider;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\Security\Token\AgentSessionSecurityToken;
use DeskPRO\Bundle\ApiBundle\Security\Token\LegacyRememberMeSecurityToken;
use DeskPRO\Bundle\AppBundle\Security\DpPersonUserProvider;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class LegacyRememberMeProvider.
 */
class LegacyRememberMeProvider implements AuthenticationProviderInterface
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\Security\DpPersonUserProvider
     */
    private $dp_person_provider;

    /**
     * @var Session
     */
    private $session;

    public function __construct(DpPersonUserProvider $dp_person_provider, Session $session)
    {
        $this->dp_person_provider = $dp_person_provider;
        $this->session            = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        /** @var LegacyRememberMeSecurityToken $token */
        if ($token->isAuthenticated()) {
            return $token;
        }

        $this->session->set('last_username', $token->getUsername());
        $credentials = $token->getCredentials();
        /** @var Person $person */
        $person = $token->getUser();

        if (!$person->validateRememberMeCookieCode($credentials)) {
            throw new AuthenticationException('could not handle old-style remember me: '.$credentials);
        }

        return new AgentSessionSecurityToken($person, $person->getPassword(), array_merge(['ROLE_USER'], $person->getRoles()));
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof LegacyRememberMeSecurityToken;
    }
}
