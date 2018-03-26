<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Model\Notifications;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;

class AgentSubscriptionSet
{
    /**
     * @var Agent
     */
    public $agent;

    /**
     * @var AgentSubscription[]
     */
    public $subscriptions = [];
}
