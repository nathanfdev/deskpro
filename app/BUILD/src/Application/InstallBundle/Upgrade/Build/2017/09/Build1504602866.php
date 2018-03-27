<?php

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
