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
