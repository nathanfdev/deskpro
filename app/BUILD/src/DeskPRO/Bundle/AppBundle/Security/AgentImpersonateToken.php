<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Security;

use Application\DeskPRO\Entity\Person;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * Represents someone logged in that is impersonating another user.
 */
class AgentImpersonateToken extends AbstractToken
{
    const ATTR_AGENT_IMPERSONATE = 'impersonating_agent_id';

    /**
     * @var string
     */
    protected $auth;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $agent;

    public function __construct($auth)
    {
        parent::__construct(['ROLE_USER']);

        $this->auth = $auth;
        // this will get set if valid $auth in AgentImpersonateProvider
        // $this->setUser(null);
        $this->setAuthenticated(false);
    }

    public function getAuth()
    {
        return $this->auth;
    }

    public function setAgent(Person $agent)
    {
        $this->agent = $agent;
        // set the attribute so we can always know who the impersonating agent is
        // only this attrbiute is useful in subsequent requests (session)
        // so don't do ->getAgent(), instead get the attribute id and fetch the agent Person object yourslef
        $this->setAttribute(self::ATTR_AGENT_IMPERSONATE, $agent->getId());
    }

    public function getAgent()
    {
        return $this->agent;
    }

    public function getCredentials()
    {
        return;
    }
}
