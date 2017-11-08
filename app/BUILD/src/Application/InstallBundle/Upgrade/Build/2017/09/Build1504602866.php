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

namespace Application\InstallBundle\Upgrade\Build;

class Build1504602866 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'TRUNCATE TABLE tickets_search_active');
        $this->execDbQuery('default', 'ALTER TABLE tickets_search_active ADD date_on_hold DATETIME DEFAULT NULL');

        // via TicketSearchActive::getFieldNames
        $fields = [
            'id', 'ref', 'language_id', 'brand_id', 'department_id', 'category_id',
            'workflow_id', 'priority_id', 'product_id', 'person_id', 'person_email_id',
            'agent_id', 'agent_team_id', 'organization_id', 'email_account_id', 'creation_system', 'creation_system_option',
            'status', 'is_hold', 'urgency', 'feedback_rating', 'date_feedback_rating',
            'date_created', 'date_resolved', 'date_on_hold', 'date_first_agent_assign', 'date_first_agent_reply',
            'date_last_agent_reply', 'date_last_user_reply', 'date_agent_waiting', 'date_user_waiting', 'date_status',
            'total_user_waiting', 'total_to_first_reply',
            'subject', 'original_subject',
        ];

        $fields = implode(',', $fields);
        $this->execDbQuery('default', "
            INSERT IGNORE INTO tickets_search_active ($fields) SELECT $fields
            FROM tickets
            WHERE status IN ('awaiting_agent', 'awaiting_user', 'resolved')
            ORDER BY id ASC
        ");
    }

    public function run()
    {
    }
}
