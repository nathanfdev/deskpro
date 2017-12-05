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

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Context\AgentContext;
use Symfony\Component\PropertyAccess\PropertyAccess;

class Context
{
    /**
     * @var AgentContext
     */
    private $agentContext;

    /**
     * @var array
     */
    private $context;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $contextAccessor;

    public function __construct(AgentContext $agentContext)
    {
        $this->agentContext = $agentContext;

        $this->context           = new \stdClass();
        $this->context->me       = $agentContext->id;
        $this->context->my_teams = $agentContext->teams;

        $this->contextAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @return int
     */
    public function getAgentId()
    {
        return $this->agentContext->id;
    }

    /**
     * @return AgentContext
     */
    public function getAgentContext()
    {
        return $this->agentContext;
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
     * @param AgentContext $agentContext
     *
     * @return Context
     */
    public static function createContext(AgentContext $agentContext)
    {
        return new self($agentContext);
    }
}
