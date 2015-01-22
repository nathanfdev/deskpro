<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
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
     * @return string|null
     */
    public function lookupForMessage(TicketMessage $message)
    {
        if (!$message->ip_address) {
            return null;
        }

        try {
            return $this->rdns->lookup($message->ip_address);
        } catch (\Exception $e) {
            return null;
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