<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1543397093 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->truncateTable('default', 'tickets_search_active');
        $this->execDbQuery('default', 'DROP INDEX status_idx ON tickets_search_active');
        $this->execDbQuery('default', 'ALTER TABLE tickets_search_active ADD ticket_status_id INT DEFAULT NULL AFTER STATUS');
        $this->execDbQuery('default', 'ALTER TABLE tickets_search_active ADD CONSTRAINT FK_9645F8AF1CDDAF7 FOREIGN KEY (ticket_status_id) REFERENCES ticket_statuses (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_9645F8AF1CDDAF7 ON tickets_search_active (ticket_status_id)');
        $this->execDbQuery('default', 'CREATE INDEX status_idx ON tickets_search_active (status, ticket_status_id)');

        // via TicketSearchActive::getFieldNames
        $fields = [
            'id', 'ref', 'language_id', 'brand_id', 'department_id', 'category_id',
            'workflow_id', 'priority_id', 'product_id', 'person_id', 'person_email_id',
            'agent_id', 'agent_team_id', 'organization_id', 'email_account_id', 'creation_system', 'creation_system_option',
            'status', 'ticket_status_id', 'urgency', 'feedback_rating', 'date_feedback_rating',
            'date_created', 'date_resolved', 'date_on_hold', 'date_first_agent_assign', 'date_first_agent_reply',
            'date_last_agent_reply', 'date_last_user_reply', 'date_agent_waiting', 'date_user_waiting', 'date_status',
            'total_user_waiting', 'total_to_first_reply',
            'subject', 'original_subject',
        ];

        $fields = implode(',', $fields);
        $this->execDbQuery('default', "
            INSERT IGNORE INTO tickets_search_active ($fields) SELECT $fields
            FROM tickets
            WHERE status IN ('awaiting_agent', 'awaiting_user', 'resolved', 'pending')
            ORDER BY id ASC
        ");
    }

    public function run()
    {
    }
}
