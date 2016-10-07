<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Security\Authentication\Provider;

use Application\DeskPRO\App;
use Application\DeskPRO\Auth\AuthenticationManager as DpAuthManager;
use DeskPRO\Bundle\AppBundle\Security\DpPersonUserProvider;
use DeskPRO\Bundle\AppBundle\Security\DpTransferSessionAuthToken;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TransferSessionAuthProvider implements AuthenticationProviderInterface
{
    /**
     * @var \Application\DeskPRO\Auth\AuthenticationManager
     */
    private $dp_auth_manager;

    /**
     * @var \DeskPRO\Bundle\AppBundle\Security\DpPersonUserProvider
     */
    private $dp_person_provider;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(DpAuthManager $dp_auth_manager, DpPersonUserProvider $dp_person_provider, Session $session, EntityManager $em)
    {
        $this->dp_auth_manager    = $dp_auth_manager;
        $this->dp_person_provider = $dp_person_provider;
        $this->session            = $session;
        $this->em                 = $em;
    }

    /**
     * Attempts to authenticate a TokenInterface object.
     *
     * @param DpTransferSessionAuthToken $token The TokenInterface instance to authenticate
     *
     * @throws AuthenticationException if the authentication fails
     *
     * @return DpTransferSessionAuthToken An authenticated TokenInterface instance, never null
     */
    public function authenticate(TokenInterface $token)
    {
        /** @var DpTransferSessionAuthToken $token */
        if ($token->isAuthenticated()) {
            return $token;
        }

        // get the auth code and extract the impersonating agent and person
        $session_id = $token->getCredentials();
        $sid        = \Application\DeskPRO\Entity\Session::getIdFromCode($session_id);
        if ($sid) {
            $agent_session = App::getDb()->fetchAssoc(
                '
                    SELECT person_id, auth
                    FROM sessions
                    WHERE id = ?
                ',
                [
                    $sid,
                ]
            );

            list(, $auth) = explode('-', $session_id);

            if ($agent_session && $agent_session['auth'] == $auth && $agent_session['person_id']) {
                if ($person = $this->em->getRepository('DeskPRO:Person')->find($agent_session['person_id'])) {
                    $token = new DpTransferSessionAuthToken($person, $session_id);

                    return $token;
                }
            }
        }

        throw new AuthenticationException('could not transfer session to portal: '.$session_id);
    }

    /**
     * Checks whether this provider supports the given token.
     *
     * @param TokenInterface $token A TokenInterface instance
     *
     * @return bool true if the implementation supports the Token, false otherwise
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof DpTransferSessionAuthToken;
    }
}
