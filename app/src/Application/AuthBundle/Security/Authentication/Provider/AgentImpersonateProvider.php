<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Portal
 */

namespace Application\AuthBundle\Security\Authentication\Provider;

use Application\AuthBundle\Security\AgentImpersonateToken;
use Application\AuthBundle\Security\DpFormLoginToken;
use Application\AuthBundle\Security\DpPersonUserProvider;
use Application\DeskPRO\Auth\AuthenticationManager;
use Application\DeskPRO\Auth\AuthenticationManager as DpAuthManager;
use Application\DeskPRO\Auth\LoginProcessor;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\ORM\EntityManager;
use DeskPRO\Kernel\KernelErrorHandler;
use Doctrine\DBAL\Driver\Connection;
use Orb\Auth\Adapter\FormLoginInterface;
use Orb\Auth\Result;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Application\DeskPRO\Entity\Person;

class AgentImpersonateProvider implements AuthenticationProviderInterface
{
    /**
     * @var \Application\DeskPRO\Auth\AuthenticationManager
     */
    private $dp_auth_manager;

    /**
     * @var \Application\AuthBundle\Security\DpPersonUserProvider
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
        $this->session = $session;
        $this->em = $em;
    }

    /**
     * Attempts to authenticate a TokenInterface object.
     *
     * @param AgentImpersonateToken $token The TokenInterface instance to authenticate
     *
     * @return AgentImpersonateToken An authenticated TokenInterface instance, never null
     *
     * @throws AuthenticationException if the authentication fails
     */
    public function authenticate(TokenInterface $token)
    {
        if ($token->isAuthenticated()) {
            return $token;
        }

        // get the auth code and extract the impersonating agent and person
        $auth = $token->getAuth();
        list($agent_id, $person_id) = $this->getAgentAndPersonIds($auth);
        list($agent, $person) = $this->getPersonEntities($agent_id, $person_id);

        $token->setUser($person);
        $token->setAgent($agent);
        $token->setAuthenticated(true);

        $this->session->invalidate();

        return $token;
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
        return $token instanceof AgentImpersonateToken;
    }

    /**
     * @param string $auth
     * @return array
     */
    private function getAgentAndPersonIds($auth)
    {
        /** @var \Application\DeskPRO\Entity\TmpData $tmp */
        $tmp = $this->em->getRepository('DeskPRO:TmpData')->getByCode($auth);
        if (!$tmp) {
            throw new AuthenticationException();
        }
        $agent_id = $tmp->getData('agent_id');
        $person_id = $tmp->getData('person_id');
        if (!$agent_id || !$person_id) {
            throw new AuthenticationException();
        }
        return array($agent_id, $person_id);
    }

    /**
     * @param $agent_id
     * @param $person_id
     * @return Person[]
     */
    private function getPersonEntities($agent_id, $person_id)
    {
        $agent_repo = $this->em->getRepository('DeskPRO:Person');
        $agent = $agent_repo->find($agent_id);
        $person = $agent_repo->find($person_id);
        if (!$agent || !$person) {
            throw new AuthenticationException();
        }
        return array($agent, $person);
    }
}
