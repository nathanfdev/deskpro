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

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

/**
 * This is just used to define constants.
 */
class Terms
{
    const TICKET_ID                    = 'ticket.id';
    const TICKET_STATUS                = 'ticket.status';
    const TICKET_DEPARTMENT            = 'ticket.department';
    const TICKET_AGENT                 = 'ticket.agent';
    const TICKET_AGENT_TEAM            = 'ticket.agent_team';
    const TICKET_FOLLOWERS             = 'ticket.followers';
    const TICKET_LANGUAGE              = 'ticket.language';
    const TICKET_PRODUCT               = 'ticket.product';
    const TICKET_CATEGORY              = 'ticket.category';
    const TICKET_PRIORITY              = 'ticket.priority';
    const TICKET_URGENCY               = 'ticket.urgency';
    const TICKET_WORKFLOW              = 'ticket.workflow';
    const TICKET_LABELS                = 'ticket.labels';
    const TICKET_EMAIL_ACCOUNT         = 'ticket.email_account';
    const TICKET_IS_HOLD               = 'ticket.is_hold';
    const TICKET_SLAS                  = 'ticket.slas';
    const TICKET_DATE_CREATED          = 'ticket.date_created';
    const TICKET_DATE_LAST_AGENT_REPLY = 'ticket.date_last_agent_reply';
    const TICKET_DATE_LAST_USER_REPLY  = 'ticket.date_last_user_reply';
    const TICKET_DATE_AGENT_WAITING    = 'ticket.date_agent_waiting';
    const TICKET_DATE_USER_WAITING     = 'ticket.date_user_waiting';
    const TICKET_CUSTOM                = 'ticket.custom.field_%s';

    const PERSON_ID         = 'ticket.person.id';
    const PERSON_LABELS     = 'ticket.person.labels';
    const PERSON_USERGROUPS = 'ticket.person.usergroups';
    const PERSON_CUSTOM     = 'ticket.person.custom.field_%s';

    const ORG_ID         = 'ticket.organization.id';
    const ORG_LABELS     = 'ticket.organization.labels';
    const ORG_USERGROUPS = 'ticket.organization.usergroups';

    const VAR_ME       = 'me.id';
    const VAR_MY_TEAM  = 'me.primary_team';
    const VAR_MY_TEAMS = 'me.teams';

    const FUNC_NOW  = 'now';
    const FUNC_DATE = 'date';

    const FUNC_PASSING_SLAS = 'passingSlas';
    const FUNC_WARNING_SLAS = 'warningSlas';
    const FUNC_FAILED_SLAS  = 'failedSlas';
}
