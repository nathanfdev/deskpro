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

namespace DeskPRO\Bundle\ApiBundle\Security\Authentication;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Session;
use DeskPRO\Bundle\ApiBundle\Request\PortalSessionReader;
use DeskPRO\Bundle\ApiBundle\Security\Token\AgentSessionSecurityToken;
use DeskPRO\Bundle\ApiBundle\Security\Token\PortalSessionSecurityToken;
use Doctrine\ORM\EntityManager;
use Orb\Util\Web;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

/**
 * Class OAuthAuthenticator.
 */
class OAuthAuthenticator implements SimplePreAuthenticatorInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var PortalSessionReader
     */
    private $portalSessionReader;

    /**
     * Constructor.
     *
     * @param EntityManager       $em
     * @param PortalSessionReader $portalSessionReader
     */
    public function __construct(EntityManager $em, PortalSessionReader $portalSessionReader)
    {
        $this->em                  = $em;
        $this->portalSessionReader = $portalSessionReader;
    }

    /**
     * {@inheritdoc}
     */
    public function createToken(Request $request, $providerKey)
    {
        // agent session cookie
        foreach (['dpsid-admin', 'dpsid-agent'] as $cookieName) {
            $sessionId = $request->cookies->get($cookieName);
            if (!$sessionId) {
                continue;
            }

            // check that session still exists
            /** @var \Application\DeskPRO\EntityRepository\Session $sessionRepo */
            $sessionRepo = $this->em->getRepository(Session::class);
            $session     = $sessionRepo->getSessionFromCode($sessionId);

            if (!$session || !$session->getPersonId()) {
                continue;
            }

            return new AgentSessionSecurityToken('anon.', $sessionId, $providerKey);
        }

        // portal session cookie
        $sessionId = $request->cookies->get('dpsid-portal', null);
        if ($sessionId && is_scalar($sessionId)) {
            return new PortalSessionSecurityToken('anon.', $sessionId, $providerKey);
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if ($token instanceof AgentSessionSecurityToken) {
            return $this->authenticateAgentSession($token, $userProvider, $providerKey);
        }
        if ($token instanceof PortalSessionSecurityToken) {
            return $this->authenticatePortalLogin($token, $userProvider, $providerKey);
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        if (!$token instanceof PreAuthenticatedToken || $token->getProviderKey() !== $providerKey) {
            return false;
        }

        return $token instanceof AgentSessionSecurityToken
            || $token instanceof PortalSessionSecurityToken;
    }

    /**
     * @param AgentSessionSecurityToken $token
     * @param UserProviderInterface     $userProvider
     * @param string                    $providerKey
     *
     * @return AgentSessionSecurityToken|void
     */
    private function authenticateAgentSession(AgentSessionSecurityToken $token, UserProviderInterface $userProvider, $providerKey)
    {
        /** @var \Application\DeskPRO\EntityRepository\Session $repository */
        $repository = $this->em->getRepository(Session::class);
        if (!$session = $repository->getSessionFromCode($token->getCredentials())) {
            return;
        }

        $data = Web::unserializeSesisonData($session->getData());
        if (!$data || empty($data['_sf2_attributes']['auth_person_id'])) {
            // we cant find the session, or data from the session, or the person id from tht data
            return;
        }

        /* @var \Application\DeskPRO\Entity\Person $person */
        try {
            $person = $userProvider->loadUserByUsername($data['_sf2_attributes']['auth_person_id']);
        } catch (UsernameNotFoundException $e) {
            // we have the person id from the session, but we cant find a person object with it
            return;
        }

        if (!$person->canAgent() && !$person->canAdmin()) {
            return;
        }

        return new AgentSessionSecurityToken(
            $person,
            $token->getCredentials(),
            $providerKey,
            $person->getRoles()
        );
    }

    /**
     * @param PortalSessionSecurityToken $token
     * @param UserProviderInterface      $userProvider
     * @param string                     $providerKey
     *
     * @return PortalSessionSecurityToken|mixed|void
     */
    private function authenticatePortalLogin(PortalSessionSecurityToken $token, UserProviderInterface $userProvider, $providerKey)
    {
        $data = $this->portalSessionReader->getFromSessionId($token->getCredentials());
        if (!isset($data['_security_portal'])) {
            return;
        }

        $portalToken = unserialize($data['_security_portal']);
        if (!$token || !$portalToken->getUser() || !$portalToken->getUser()->getId()) {
            return;
        }

        // refresh person from db
        $person = $this->em->getRepository(Person::class)->find($portalToken->getUser()->getId());
        if (!$person) {
            return;
        }

        return new PortalSessionSecurityToken(
            $person,
            $token->getCredentials(),
            $providerKey,
            $portalToken->getUser()->getRoles()
        );
    }
}
