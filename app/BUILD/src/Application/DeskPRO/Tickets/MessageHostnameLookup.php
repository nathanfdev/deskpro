<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Net\Dns\Rdns\RdnsInterface;

class MessageHostnameLookup
{
    /**
     * @var RdnsInterface
     */
    private $rdns;

    /**
     * @param RdnsInterface $rdns
     */
    public function __construct(RdnsInterface $rdns)
    {
        $this->rdns = $rdns;
    }

    /**
     * @param TicketMessage $message
     *
     * @return string|null
     */
    public function lookupForMessage(TicketMessage $message)
    {
        if (!$message->ip_address) {
            return;
        }

        try {
            return $this->rdns->lookup($message->ip_address);
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * @return RdnsInterface
     */
    public function getRdns()
    {
        return $this->rdns;
    }
}
