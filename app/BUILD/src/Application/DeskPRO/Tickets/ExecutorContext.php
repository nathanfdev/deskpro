<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets;

class ExecutorContext extends AbstractExecutorContext
{
    /**
     * Ticket events
     */
    const EVENT_NEW    = 'newticket';
    const EVENT_REPLY  = 'newreply';
    const EVENT_UPDATE = 'update';
    const EVENT_DELETE = 'delete';
    const EVENT_NOOP   = 'noop';
}
