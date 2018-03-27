<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

interface ExecutionContextAware
{
    public function setExecutionContext($context);
}
