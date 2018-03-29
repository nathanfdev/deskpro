<?php

namespace DeskPRO\Bundle\AppBundle\Security\Authentication\Provider;

use Application\DeskPRO\App;
use DeskPRO\Bundle\AppBundle\Security\DpPersonUserProvider;
use DeskPRO\Bundle\AppBundle\Security\DpTransferSessionAuthToken;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class TransferSessionAuthProvider.
 */
class TransferSessionAuthProvider implements AuthenticationProviderInterface
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\Security\DpPersonUserProvider
     */
    private $dpPersonProvider;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param DpPersonUserProvider $dpPersonProvider
     * @param Session              $session
     * @param EntityManager        $em
     */
    public function __construct(DpPersonUserProvider $dpPersonProvider, Session $session, EntityManager $em)
    {
        $this->dpPersonProvider = $dpPersonProvider;
        $this->session          = $session;
        $this->em               = $em;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof DpTransferSessionAuthToken;
    }
}
