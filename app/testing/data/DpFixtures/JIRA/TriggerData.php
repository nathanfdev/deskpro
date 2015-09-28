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

namespace DpFixtures\JIRA;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class TriggerData extends AbstractFixture
{
    const LABEL_STATUS      = 'jira-test-status-change';
    const LABEL_COMMENT     = 'jira-test-new-comment';
    const NEW_ISSUE_COMMENT = 'jira-test-linked-issue';

    public function load(ObjectManager $manager)
    {
        /** @var \Application\DeskPRO\DBAL\Connection $connection */
        $connection = $manager->getConnection();

        $label_status  = self::LABEL_STATUS;
        $label_comment = self::LABEL_COMMENT;
        $issue_comment = self::NEW_ISSUE_COMMENT;
        $sql           =
<<<SQL
INSERT INTO `ticket_triggers` (`department_id`, `email_account_id`, `title`, `event_trigger`, `event_flags`, `by_agent_mode`, `by_user_mode`, `is_enabled`, `is_hidden`, `is_editable`, `sys_name`, `terms`, `actions`, `run_order`, `by_app_mode`)
VALUES
(NULL, NULL, 'jira-test-trigger-status-changed', 'update', 'run_newreply', 'api,email,web', 'api,email,form,portal,widget', 1, 0, 1, NULL, '{\"@CLASS\":\"Application\\\\\\\\DeskPRO\\\\\\\\Tickets\\\\\\\\Triggers\\\\\\\\TriggerTerms\",\"@DATA\":{\"version\":1,\"terms\":[{\"set_terms\":[{\"type\":\"CheckJIRAIssueStatus\",\"op\":\"changed\",\"options\":{\"all\":null,\"status\":null}}]}]}}', '{\"@CLASS\":\"Application\\\\\\\\DeskPRO\\\\\\\\Tickets\\\\\\\\Triggers\\\\\\\\TriggerActions\",\"@DATA\":{\"version\":1,\"actions\":[{\"type\":\"SetLabels\",\"options\":{\"add_labels\":[\"{$label_status}\"],\"remove_labels\":[]}}]}}', 1040, 'deskpro_jira.1.issue_delete,deskpro_jira.1.issue_update'),
(NULL, NULL, 'jira-test-trigger-on-new-comment', 'update', 'run_newreply', 'api,email,web', 'api,email,form,portal,widget', 1, 0, 1, NULL, '{\"@CLASS\":\"Application\\\\\\\\DeskPRO\\\\\\\\Tickets\\\\\\\\Triggers\\\\\\\\TriggerTerms\",\"@DATA\":{\"version\":1,\"terms\":[{\"set_terms\":[{\"type\":\"CheckJIRANewComment\",\"op\":\"isset\",\"options\":{\"message\":\"\"}}]}]}}', '{\"@CLASS\":\"Application\\\\\\\\DeskPRO\\\\\\\\Tickets\\\\\\\\Triggers\\\\\\\\TriggerActions\",\"@DATA\":{\"version\":1,\"actions\":[{\"type\":\"SetLabels\",\"options\":{\"add_labels\":[\"{$label_comment}\"],\"remove_labels\":[]}}]}}', 1050, 'deskpro_jira.1.issue_delete,deskpro_jira.1.issue_update'),
(NULL, NULL, 'jira-test-trigger-on-new-linked-issue', 'update', 'run_newreply', 'api,email,web', 'api,email,form,portal,widget', 1, 0, 1, NULL, '{\"@CLASS\":\"Application\\\\\\\\DeskPRO\\\\\\\\Tickets\\\\\\\\Triggers\\\\\\\\TriggerTerms\",\"@DATA\":{\"version\":1,\"terms\":[{\"set_terms\":[{\"type\":\"CheckJIRANewLinkedIssue\",\"op\":\"is\",\"options\":{\"project\":\"10200\"}}]}]}}', '{\"@CLASS\":\"Application\\\\\\\\DeskPRO\\\\\\\\Tickets\\\\\\\\Triggers\\\\\\\\TriggerActions\",\"@DATA\":{\"version\":1,\"actions\":[{\"type\":\"AddJIRAComment\",\"options\":{\"note_text\":\"{$issue_comment}\",\"by_assigned_agent\":true,\"by_agent_id\":1}}]}}', 1060, 'deskpro_jira.1.issue_delete,deskpro_jira.1.issue_update');
SQL;
        $connection->executeQuery($sql);
    }
}
