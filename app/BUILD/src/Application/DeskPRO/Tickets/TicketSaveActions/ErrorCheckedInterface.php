<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketSaveActions;

/**
 * Interface added to TicketSaveActions when the actions are to be wrapped in a try/catch.
 * This is to "sandbox" certain actions like filters/triggers.
 *
 * It's a precaution meant to prevent fatal errors from bubbling up and preventing a complete loss
 * of a ticket.
 */
interface ErrorCheckedInterface
{
}
