<?php

namespace DeskPRO\Bundle\AppBundle\Security\Authentication\Provider;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Security\AgentImpersonateToken;
use DeskPRO\Bundle\AppBundle\Security\DpPersonUserProvider;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class AgentImpersonateProvider.
 */
class AgentImpersonateProvider implements AuthenticationProviderInterface
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
        if ($token->isAuthenticated()) {
            return $token;
        }

        // get the auth code and extract the impersonating agent and person
        $auth                       = $token->getAuth();
        list($agent_id, $person_id) = $this->getAgentAndPersonIds($auth);
        list($agent, $person)       = $this->getPersonEntities($agent_id, $person_id);

        $token->setUser($person);
        $token->setAgent($agent);
        $token->setAuthenticated(true);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof AgentImpersonateToken;
    }

    /**
     * @param string $auth
     *
     * @return array
     */
    private function getAgentAndPersonIds($auth)
    {
        /** @var \Application\DeskPRO\Entity\TmpData $tmp */
        $tmp = $this->em->getRepository('DeskPRO:TmpData')->getByCode($auth);
        if (!$tmp) {
            throw new AuthenticationException();
        }
        $agent_id  = $tmp->getData('agent_id');
        $person_id = $tmp->getData('person_id');
        if (!$agent_id || !$person_id) {
            throw new AuthenticationException();
        }

        return [$agent_id, $person_id];
    }

    /**
     * @param $agent_id
     * @param $person_id
     *
     * @return Person[]
     */
    private function getPersonEntities($agent_id, $person_id)
    {
        $agent_repo = $this->em->getRepository('DeskPRO:Person');
        $agent      = $agent_repo->find($agent_id);
        $person     = $agent_repo->find($person_id);
        if (!$agent || !$person) {
            throw new AuthenticationException();
        }

        return [$agent, $person];
    }
}
