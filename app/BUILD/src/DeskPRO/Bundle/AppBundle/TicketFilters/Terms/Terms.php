<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use DeskPRO\Component\Util\StringUtils;

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
    const TICKET_CUSTOM                = 'ticket.data.%s';

    const PERSON_ID         = 'ticket.person.id';
    const PERSON_LABELS     = 'ticket.person.labels';
    const PERSON_USERGROUPS = 'ticket.person.usergroups';
    const PERSON_CUSTOM     = 'ticket.person.data.%s';

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

    /**
     * Given an ID, check if it's a custom field.
     *
     * @param string $fieldId
     *
     * @return array [type, customFieldId] if not found, then values will be null
     */
    public static function parseCustomFieldId($fieldId)
    {
        if ($fieldAlias = StringUtils::removeFromStart(sprintf(self::TICKET_CUSTOM, ''), $fieldId)) {
            return ['ticket.data', $fieldAlias];
        } elseif ($fieldAlias = StringUtils::removeFromStart(sprintf(self::PERSON_CUSTOM, ''), $fieldId)) {
            return ['ticket.person.data', $fieldAlias];
        } else {
            return [null, null];
        }
    }
}
