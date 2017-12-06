<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
