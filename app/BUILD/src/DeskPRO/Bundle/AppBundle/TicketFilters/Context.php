<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class Context.
 */
class Context
{
    /**
     * @var Agent
     */
    private $agent;

    /**
     * @var array
     */
    private $context;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $contextAccessor;

    /**
     * Constructor.
     *
     * @param Agent $agent
     */
    public function __construct(Agent $agent)
    {
        $this->agent = $agent;

        $this->context           = new \stdClass();
        $this->context->me       = $agent->id;
        $this->context->my_teams = $agent->teams;

        $this->contextAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @return int
     */
    public function getAgentId()
    {
        return $this->agent->id;
    }

    /**
     * @return Agent
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param string $id
     *
     * @return mixed
     */
    public function getContextVariable($id)
    {
        return $this->contextAccessor->getValue($this->context, $id);
    }

    /**
     * @param Agent $agent
     *
     * @return Context
     */
    public static function createContext(Agent $agent)
    {
        return new self($agent);
    }
}
