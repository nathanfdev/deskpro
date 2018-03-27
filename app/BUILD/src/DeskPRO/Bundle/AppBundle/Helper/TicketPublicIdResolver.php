<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Helper;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\NewSettings\SettingsResolver;

/**
 * Find the correct public ID to display (or use in URLs) for any given ticket.
 */
class TicketPublicIdResolver
{
    /**
     * @var SettingsResolver
     */
    private $settings_resolver;

    public function __construct(SettingsResolver $settings_resolver)
    {
        $this->settings_resolver = $settings_resolver;
    }

    public function findId(Ticket $ticket)
    {
        if ($this->settings_resolver->getGlobalSettings()->get('core_tickets.use_ref')) {
            $ref = $ticket->getRef();
        } else {
            $ref = $ticket->getId();
        }

        return $ref;
    }
}
