<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Ticket;

/**
 * A class can bounce-aware if it needs to change functionality based on
 * if an email was detected as a bounce or not.
 */
interface BounceAwareInterface
{
    public function enableBouncedMode();
}
