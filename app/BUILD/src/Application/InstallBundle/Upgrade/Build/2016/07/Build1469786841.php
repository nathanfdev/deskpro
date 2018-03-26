<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1469786841 extends AbstractBuild
{
    public function run()
    {
        $this->out('Recreate tickets_search_active');
        $this->execDbQuery('default', 'DROP TABLE tickets_search_active');
        $this->execDbQuery('default', 'CREATE TABLE tickets_search_active (id INT NOT NULL, ref VARCHAR(100) NOT NULL, language_id INT DEFAULT NULL, brand_id INT DEFAULT NULL, department_id INT DEFAULT NULL, category_id INT DEFAULT NULL, workflow_id INT DEFAULT NULL, priority_id INT DEFAULT NULL, product_id INT DEFAULT NULL, person_id INT DEFAULT NULL, person_email_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, agent_team_id INT DEFAULT NULL, organization_id INT DEFAULT NULL, sent_to_address VARCHAR(200) NOT NULL, creation_system VARCHAR(100) NOT NULL, creation_system_option VARCHAR(1000) NOT NULL, status VARCHAR(30) NOT NULL, is_hold TINYINT(1) NOT NULL, urgency INT NOT NULL, feedback_rating INT DEFAULT NULL, date_feedback_rating DATETIME DEFAULT NULL, date_created DATETIME NOT NULL, date_resolved DATETIME DEFAULT NULL, date_first_agent_assign DATETIME DEFAULT NULL, date_first_agent_reply DATETIME DEFAULT NULL, date_last_agent_reply DATETIME DEFAULT NULL, date_last_user_reply DATETIME DEFAULT NULL, date_agent_waiting DATETIME DEFAULT NULL, date_user_waiting DATETIME DEFAULT NULL, date_status DATETIME NOT NULL, total_user_waiting INT NOT NULL, total_to_first_reply INT NOT NULL, subject VARCHAR(255) NOT NULL, original_subject VARCHAR(255) NOT NULL, INDEX date_created_idx (date_created), INDEX status_idx (status), INDEX person_idx (person_id), INDEX agent_idx (agent_id), INDEX ref_idx (ref), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');

        // via TicketSearchActive::getFieldNames
        $fields = [
            'id', 'ref', 'language_id', 'brand_id', 'department_id', 'category_id', 'workflow_id', 'priority_id',
            'product_id', 'person_id', 'person_email_id', 'agent_id', 'agent_team_id', 'organization_id',
            'creation_system', 'creation_system_option', 'status', 'is_hold', 'urgency', 'feedback_rating',
            'date_feedback_rating', 'date_created', 'date_resolved', 'date_first_agent_assign', 'date_first_agent_reply',
            'date_last_agent_reply', 'date_last_user_reply', 'date_agent_waiting', 'date_user_waiting', 'date_status',
            'total_user_waiting', 'total_to_first_reply', 'subject', 'original_subject',
        ];

        $fields = implode(',', $fields);
        $this->execDbQuery('default', "
            INSERT IGNORE INTO tickets_search_active ($fields) SELECT $fields
            FROM tickets
            WHERE status IN ('awaiting_agent', 'awaiting_user', 'resolved')
            ORDER BY id ASC
        ");
    }
}
