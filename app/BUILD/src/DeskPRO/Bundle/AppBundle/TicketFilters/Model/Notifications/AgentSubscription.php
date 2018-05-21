<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Model\Notifications;

class AgentSubscription
{
    const TYPE_EMAIL   = 'email';
    const TYPE_BROWSER = 'browser';
    const TYPE_MOBILE  = 'mobile';

    const ASSIGN_ME      = 'assign_me';
    const ASSIGN_MY_TEAM = 'assign_my_team';
    const ASSIGN_FOLLOW  = 'assign_follow';

    const NEW_ANY = 'new_any';

    const REPLY_ME      = 'reply_me';
    const REPLY_MY_TEAM = 'reply_my_team';
    const REPLY_FOLLOW  = 'reply_follow';
    const REPLY_ANY     = 'reply_any';

    /**
     * @var string
     */
    public $type;

    /**
     * Array of key events the agent is subscribed to.
     *
     * @var array
     */
    public $events = [];

    /**
     * Array of filter events the agent is subscribed to.
     *
     * @var AgentFilterSubscriptionEvent[]
     */
    public $filterEvents = [];
}
