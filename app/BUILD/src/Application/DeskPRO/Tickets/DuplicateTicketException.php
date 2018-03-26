<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets;

/**
 * This just looks at a filter and agents to determine who is able to use a filter,
 * and who is actually using it (based on prefs).
 */
class DuplicateTicketException extends \Exception
{
    /** @var int|null */
    public $ticket_id = null;
}
