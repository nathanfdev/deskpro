<?php

namespace DpFixtures\JIRA;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class TriggerData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        /** @var \Application\DeskPRO\DBAL\Connection $connection */
        $connection = $manager->getConnection();

        $sql =
<<<SQL
INSERT INTO `ticket_triggers` (`department_id`, `email_account_id`, `title`, `event_trigger`, `event_flags`, `by_agent_mode`, `by_user_mode`, `is_enabled`, `is_hidden`, `is_editable`, `sys_name`, `terms`, `actions`, `run_order`, `by_app_mode`)
VALUES
(NULL, NULL, 'jira-test-trigger-status-changed', 'update', 'run_newreply', 'api,email,web', 'api,email,form,portal,widget', 1, 0, 1, NULL, '{\"@CLASS\":\"Application\\\\DeskPRO\\\\Tickets\\\\Triggers\\\\TriggerTerms\",\"@DATA\":{\"version\":1,\"terms\":[{\"set_terms\":[{\"type\":\"CheckJIRAIssueStatus\",\"op\":\"changed\",\"options\":{\"all\":null,\"status\":null}}]}]}}', '{\"@CLASS\":\"Application\\\\DeskPRO\\\\Tickets\\\\Triggers\\\\TriggerActions\",\"@DATA\":{\"version\":1,\"actions\":[{\"type\":\"SetLabels\",\"options\":{\"add_labels\":[\"jira-test-status-change\"],\"remove_labels\":[]}}]}}', 1040, 'deskpro_jira.1.issue_delete,deskpro_jira.1.issue_update'),
(NULL, NULL, 'jira-test-trigger-on-new-comment', 'update', 'run_newreply', 'api,email,web', 'api,email,form,portal,widget', 1, 0, 1, NULL, '{\"@CLASS\":\"Application\\\\DeskPRO\\\\Tickets\\\\Triggers\\\\TriggerTerms\",\"@DATA\":{\"version\":1,\"terms\":[{\"set_terms\":[{\"type\":\"CheckJIRANewComment\",\"op\":\"isset\",\"options\":{\"message\":\"\"}}]}]}}', '{\"@CLASS\":\"Application\\\\DeskPRO\\\\Tickets\\\\Triggers\\\\TriggerActions\",\"@DATA\":{\"version\":1,\"actions\":[{\"type\":\"SetLabels\",\"options\":{\"add_labels\":[\"jira-test-new-comment\"],\"remove_labels\":[]}}]}}', 1050, 'deskpro_jira.1.issue_delete,deskpro_jira.1.issue_update'),
(NULL, NULL, 'jira-test-trigger-on-new-linked-issue', 'update', 'run_newreply', 'api,email,web', 'api,email,form,portal,widget', 1, 0, 1, NULL, '{\"@CLASS\":\"Application\\\\DeskPRO\\\\Tickets\\\\Triggers\\\\TriggerTerms\",\"@DATA\":{\"version\":1,\"terms\":[{\"set_terms\":[{\"type\":\"CheckJIRANewLinkedIssue\",\"op\":\"is\",\"options\":{\"project\":\"10200\"}}]}]}}', '{\"@CLASS\":\"Application\\\\DeskPRO\\\\Tickets\\\\Triggers\\\\TriggerActions\",\"@DATA\":{\"version\":1,\"actions\":[{\"type\":\"AddJIRAComment\",\"options\":{\"note_text\":\"jira-test-linked-issue\",\"by_assigned_agent\":true,\"by_agent_id\":1}}]}}', 1060, 'deskpro_jira.1.issue_delete,deskpro_jira.1.issue_update');
SQL;
        $connection->executeQuery($sql);
    }
}
