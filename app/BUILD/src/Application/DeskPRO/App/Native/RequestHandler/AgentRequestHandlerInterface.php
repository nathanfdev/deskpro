<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\App\Native\RequestHandler;

interface AgentRequestHandlerInterface
{
    /**
     * @param AgentRequestContext $context
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleAgentRequest(AgentRequestContext $context);
}
