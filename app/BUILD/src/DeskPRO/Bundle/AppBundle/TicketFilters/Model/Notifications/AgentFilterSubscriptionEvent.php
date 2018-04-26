<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Model\Notifications;

class AgentFilterSubscriptionEvent
{
    const FILTER_MATCH = 'filter_match';
    const FILTER_REPLY = 'filter_reply';

    /**
     * The filter id.
     *
     * @var int
     */
    public $filter = 0;

    /**
     * Array of events subscribed to.
     *
     * @var array
     */
    public $events = [];
}
