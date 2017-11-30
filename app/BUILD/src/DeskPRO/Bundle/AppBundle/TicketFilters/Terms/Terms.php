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

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\CustomField;
use DeskPRO\Component\FilterQueryLanguage\AbstractQueryDefinition;

class Terms extends AbstractQueryDefinition
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

    const FUNC_PASSING_SLAS = 'passingSlas';
    const FUNC_WARNING_SLAS = 'warningSlas';
    const FUNC_FAILED_SLAS  = 'failedSlas';

    /**
     * @var CustomField[]
     */
    private $customTicketFields = [];

    /**
     * @var CustomField[]
     */
    private $customPersonFields = [];

    /**
     * Terms constructor.
     *
     * @param CustomField[] $customTicketFields
     * @param CustomField[] $customPersonFields
     */
    public function __construct(array $customTicketFields = [], array $customPersonFields = [])
    {
        $this->customTicketFields = $customTicketFields;
        $this->customPersonFields = $customPersonFields;
    }

    /**
     * @return array
     */
    public function getFieldDefs()
    {
        $defs = [
            self::TICKET_ID            => ['type' => 'ID'],
            self::TICKET_STATUS        => ['type' => 'STRING'],
            self::TICKET_DEPARTMENT    => ['type' => 'ID'],
            self::TICKET_AGENT         => ['type' => 'ID'],
            self::TICKET_AGENT_TEAM    => ['type' => 'ID'],
            self::TICKET_FOLLOWERS     => ['type' => 'ID[]'],
            self::TICKET_LANGUAGE      => ['type' => 'ID'],
            self::TICKET_PRODUCT       => ['type' => 'ID'],
            self::TICKET_CATEGORY      => ['type' => 'ID'],
            self::TICKET_PRIORITY      => ['type' => 'ID'],
            self::TICKET_URGENCY       => ['type' => 'ID'],
            self::TICKET_WORKFLOW      => ['type' => 'ID'],
            self::TICKET_LABELS        => ['type' => 'STRING[]'],
            self::TICKET_EMAIL_ACCOUNT => ['type' => 'ID'],
            self::TICKET_IS_HOLD       => ['type' => 'BOOLEAN'],

            self::TICKET_SLAS => ['type' => '[]'],

            self::TICKET_DATE_CREATED          => ['type' => 'DATE'],
            self::TICKET_DATE_LAST_AGENT_REPLY => ['type' => 'DATE'],
            self::TICKET_DATE_LAST_USER_REPLY  => ['type' => 'DATE'],
            self::TICKET_DATE_AGENT_WAITING    => ['type' => 'DATE'],
            self::TICKET_DATE_USER_WAITING     => ['type' => 'DATE'],
            self::PERSON_ID                    => ['type' => 'INT'],
            self::PERSON_LABELS                => ['type' => 'STRING[]'],
            self::PERSON_USERGROUPS            => ['type' => 'ID[]'],
            self::ORG_ID                       => ['type' => 'ID'],
            self::ORG_LABELS                   => ['type' => 'STRING[]'],
            self::ORG_USERGROUPS               => ['type' => 'ID[]'],
        ];

        foreach ($this->customTicketFields as $f) {
            $defs[sprintf(self::TICKET_CUSTOM, $f->field)] = ['type' => $f->valueType];
        }
        foreach ($this->customPersonFields as $f) {
            $defs[sprintf(self::PERSON_CUSTOM, $f->field)] = ['type' => $f->valueType];
        }

        return $defs;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariableDefs()
    {
        return [
            self::VAR_ME       => ['type' => 'INT'],
            self::VAR_MY_TEAM  => ['type' => 'INT'],
            self::VAR_MY_TEAMS => ['type' => 'INT[]'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctionDefs()
    {
        return [
            self::FUNC_FAILED_SLAS => [
                'fields'    => [self::TICKET_SLAS],
                'operators' => ['IN', 'NOT_IN', 'EMPTY', 'NOT_EMPTY'],
                'params'    => [],
            ],
            self::FUNC_WARNING_SLAS => [
                'fields'    => [self::TICKET_SLAS],
                'operators' => ['IN', 'NOT_IN', 'EMPTY', 'NOT_EMPTY'],
                'params'    => [],
            ],
            self::FUNC_FAILED_SLAS => [
                'fields'    => [self::TICKET_SLAS],
                'operators' => ['IN', 'NOT_IN', 'EMPTY', 'NOT_EMPTY'],
                'params'    => [],
            ],
        ];
    }
}
