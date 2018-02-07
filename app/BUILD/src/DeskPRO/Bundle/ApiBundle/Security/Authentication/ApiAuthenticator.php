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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\ApiBundle\Security\Authentication;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Session;
use DeskPRO\Bundle\ApiBundle\Security\Token\AgentSessionSecurityToken;
use DeskPRO\Bundle\ApiBundle\Security\Token\ApiKeySecurityToken;
use DeskPRO\Bundle\ApiBundle\Security\Token\ApiTokenSecurityToken;
use DeskPRO\Bundle\ApiBundle\Security\Token\LegacyRememberMeSecurityToken;
use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use Doctrine\ORM\EntityManager;
use Orb\Util\Web;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

class ApiAuthenticator implements SimplePreAuthenticatorInterface
{
    const HTTP_REALM      = 'session,token,key realm="DeskPRO API"';
    const APP_HEADER_NAME = 'X-DeskPRO-App-ID';

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function createToken(Request $request, $providerKey)
    {
        // agent session cookie
        /** @var \Application\DeskPRO\EntityRepository\Session $sessionRepo */
        $sessionRepo = $this->em->getRepository(Session::class);

        foreach (['dpsid-admin', 'dpsid-agent'] as $cookieName) {
            $sessionId = $request->cookies->get($cookieName);
            if (!$sessionId) {
                continue;
            }

            // check that session still exists
            $session = $sessionRepo->getSessionFromCode($sessionId);
            if (!$session) {
                continue;
            }

            $agentToken = new AgentSessionSecurityToken('anon.', $sessionId, $providerKey);

            // add app if possible
            $appId = $request->headers->get(self::APP_HEADER_NAME, null, true);
            if ($appId) {
                $agentToken->setAppId($appId);
            }

            return $agentToken;
        }

        // Authorize header
        if ($authorize_header = $request->headers->get('Authorization', null, true)) {
            $split = preg_split("/[\s,]+/", trim($authorize_header));

            if (count($split) !== 2) {
                $this->throwUnauthorized(ErrorsCodes::MALFORMED_AUTHORIZATION_HEADER);
            }

            $authorize_type = trim($split[0]);
            $authorize_val  = trim($split[1]);

            switch ($authorize_type) {
                case 'key':
                    return new ApiKeySecurityToken('anon.', $authorize_val, $providerKey);
                case 'token':
                    return new ApiTokenSecurityToken('anon.', $authorize_val, $providerKey);
                default:
                    $this->throwUnauthorized(ErrorsCodes::INVALID_AUTHORIZATION_HEADER);
            }
        }

        if ($request->cookies->get('dpreme')) {
            list($person_id, $cookie_code) = explode('-', $request->cookies->get('dpreme'), 2);
            if ($person_id) {
                return new LegacyRememberMeSecurityToken($person_id, $cookie_code, $providerKey);
            }
        }

        // if we are in apache we send a special error message, because apache removes the
        // Authorization header in some cgi cases:
        // http://stackoverflow.com/questions/17488656/zend-server-windows-authorization-header-is-not-passed-to-php-script
        if (function_exists('apache_get_version') && false !== apache_get_version()) {
            if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $this->throwUnauthorized(ErrorsCodes::UNAUTHORIZED_CHECK_APACHE);
            }
        }

        $this->throwUnauthorized(ErrorsCodes::UNAUTHORIZED);
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if ($token instanceof AgentSessionSecurityToken) {
            return $this->authenticateAgentSession($token, $userProvider, $providerKey);
        }

        if ($token instanceof LegacyRememberMeSecurityToken) {
            return $this->authenticateRememberMe($token, $userProvider, $providerKey);
        }

        if ($token instanceof ApiKeySecurityToken) {
            return $this->authenticateApiKey($token, $userProvider, $providerKey);
        }

        if ($token instanceof ApiTokenSecurityToken) {
            return $this->authenticateApiToken($token, $userProvider, $providerKey);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        if (!$token instanceof PreAuthenticatedToken || $token->getProviderKey() !== $providerKey) {
            return false;
        }

        return
            $token instanceof AgentSessionSecurityToken
            || $token instanceof ApiKeySecurityToken
            || $token instanceof ApiTokenSecurityToken
            || $token instanceof LegacyRememberMeSecurityToken
            ;
    }

    protected function authenticateApiKey(
        ApiKeySecurityToken $token,
        UserProviderInterface $user_provider,
        $providerKey
    ) {
        $unauthorized_msg = ErrorsCodes::INVALID_API_KEY;

        /* @var \Application\DeskPRO\Entity\ApiKey $key */
        /** @var \Application\DeskPRO\EntityRepository\ApiKey $key_repo */
        $key_repo = $this->em->getRepository('DeskPRO:ApiKey');

        if (!$key = $key_repo->findByKeyString($token->getCredentials())) {
            $this->throwUnauthorized($unauthorized_msg);
        }

        if (!$key->person) {
            $this->throwUnauthorized($unauthorized_msg);
        }

        return new ApiKeySecurityToken(
            $key->person,
            $token->getCredentials(),
            $providerKey,
            $this->generateApiRolesForPerson($key->person)
        );
    }

    protected function authenticateApiToken(
        ApiTokenSecurityToken $token,
        UserProviderInterface $user_provider,
        $providerKey
    ) {
        $unauthorized_msg = ErrorsCodes::INVALID_API_TOKEN;

        /* @var \Application\DeskPRO\Entity\ApiToken $api_token */
        /** @var \Application\DeskPRO\EntityRepository\ApiToken $token_repo */
        $token_repo = $this->em->getRepository('DeskPRO:ApiToken');

        if (!$api_token = $token_repo->findByTokenString($token->getCredentials())) {
            $this->throwUnauthorized($unauthorized_msg);
        }

        if (!$api_token->person) {
            $this->throwUnauthorized($unauthorized_msg);
        }

        return new ApiTokenSecurityToken(
            $api_token->person,
            $token->getCredentials(),
            $providerKey,
            $this->generateApiRolesForPerson($api_token->person)
        );
    }

    private function authenticateAgentSession(
        AgentSessionSecurityToken $token,
        UserProviderInterface $user_provider,
        $providerKey
    ) {
        $unauthorized_msg = ErrorsCodes::INVALID_SESSION_ID;

        /* @var \Application\DeskPRO\Entity\Session $session */
        /** @var \Application\DeskPRO\EntityRepository\Session $session_repo */
        $session_repo = $this->em->getRepository('DeskPRO:Session');
        if (!$session = $session_repo->getSessionFromCode($token->getCredentials())) {
            $this->throwUnauthorized($unauthorized_msg);
        }

        $data = Web::unserializeSesisonData($session->getData());
        if (!$data) {
            // we cant find the session, or data from the session, or the person id from tht data
            $this->throwUnauthorized($unauthorized_msg);
        }

        if (!isset($data['_sf2_attributes']['auth_person_id'])) {
            $this->throwUnauthorized($unauthorized_msg);
        }

        if (!$person_id = $data['_sf2_attributes']['auth_person_id']) {
            $this->throwUnauthorized($unauthorized_msg);
        }

        /* @var \Application\DeskPRO\Entity\Person $person */
        try {
            $person = $user_provider->loadUserByUsername($person_id);
        } catch (UsernameNotFoundException $e) {
            // we have the person id from the session, but we cant find a person object with it
            $this->throwUnauthorized($unauthorized_msg);
        }

        if (!$person->can_agent && !$person->can_admin) {
            $this->throwUnauthorized($unauthorized_msg);
        }

        $agent_token = new AgentSessionSecurityToken(
            $person,
            $token->getCredentials(),
            $providerKey,
            $this->generateApiRolesForPerson($person)
        );

        if ($app_id = $token->getAppId()) {
            $agent_token->setAppId($app_id);
        }

        return $agent_token;
    }

    private function authenticateRememberMe(
        LegacyRememberMeSecurityToken $token,
        UserProviderInterface $user_provider,
        $providerKey
    ) {
        /** @var Person $person */
        $person = $user_provider->loadUserByUsername($token->getUser());
        if (
            $person
            && !$person->is_deleted
            && !$person->is_disabled
            && $person->validateRememberMeCookieCode($token->getCredentials())
        ) {
            return new LegacyRememberMeSecurityToken(
                $person,
                $token->getCredentials(),
                $providerKey,
                $this->generateApiRolesForPerson($person)
            );
        }
    }

    /**
     * @param $msg
     */
    private function throwUnauthorized($msg)
    {
        throw new UnauthorizedHttpException(self::HTTP_REALM, $msg);
    }

    /**
     * @param $person
     *
     * @return array
     */
    private function generateApiRolesForPerson(Person $person)
    {
        return array_merge($person->getRoles(), ['ROLE_API']);
    }
}
