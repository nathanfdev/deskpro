<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\ApiBundle\DataSerializer\DataTransformer;

use DeskPRO\Bundle\ApiBundle\DataSerializer\DataTransformerRequest;

class TicketTransformer extends AbstractDataSerializerTransformer
{
    public function getAutomaticProperties(DataTransformerRequest $transformation_request)
    {
        return [
            'id',
            'ref',
            'auth',
            'parent_ticket',
            'language',
            'department',
            'category',
            'priority',
            'workflow',
            'product',
            'person',
            'person_email',
            'person_email_validating',
            'agent',
            'agent_team',
            'organization',
            'linked_chat',
            'attachments',
            'access_codes',
            'messages',
            'sms_messages',
            'custom_data',
            'labels',
            // 'sent_to_address',
            'email_account',
            'email_account_address',
            'creation_system',
            'creation_system_option',
            'ticket_hash',
            'status',
            'hidden_status',
            'validating',
            'is_hold',
            'urgency',
            'feedback_rating',
            'date_feedback_rating',
            'date_created',
            'date_resolved',
            'date_archived',
            'date_first_agent_assign',
            'date_first_agent_reply',
            'date_last_agent_reply',
            'date_last_user_reply',
            'date_agent_waiting',
            'date_user_waiting',
            'date_status',
            'total_user_waiting',
            'total_to_first_reply',
            'locked_by_agent',
            'date_locked',
            'has_attachments',
            'subject',
            'original_subject',
            'properties',
            'count_agent_replies',
            'count_user_replies',
            'worst_sla_status',
            'waiting_times',
            'participants',
            'charges',
            'ticket_slas',
            'jira_issues',
        ];
    }

    public function getCustomProperties(DataTransformerRequest $transformation_request)
    {
        /** @var \DeskPRO\Bundle\AppBundle\Entity\Task $data */
        $data = $transformation_request->getDataToBeTransformed();

        return [
            'sent_to_address' => $data['sent_to_address'],
        ];
    }
}
