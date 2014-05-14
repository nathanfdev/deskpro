<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

class Build1396876010 extends AbstractBuild
{
	public function run()
	{
		$db = $this->container->getDb();

		$this->out("Create auditlog table");
		$db->exec("CREATE TABLE auditlog (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, person_name VARCHAR(255) NOT NULL, op VARCHAR(20) NOT NULL, object_type VARCHAR(255) NOT NULL, object_id VARCHAR(255) NOT NULL, data LONGBLOB DEFAULT NULL COMMENT '(DC2Type:array)', date_created DATETIME NOT NULL, INDEX IDX_54575EAC217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$db->exec("ALTER TABLE auditlog ADD CONSTRAINT FK_54575EAC217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL");

		$this->out("Create log_request_stats table");
		$db->exec("CREATE TABLE log_request_stats (id INT AUTO_INCREMENT NOT NULL, date_created DATETIME NOT NULL, request_id VARCHAR(100) NOT NULL, user_agent VARCHAR(255) NOT NULL, user_ip VARCHAR(255) NOT NULL, page_id VARCHAR(255) NOT NULL, page_url VARCHAR(1000) NOT NULL, response_type VARCHAR(100) NOT NULL, response_code INT NOT NULL, response_size INT NOT NULL, query_count INT NOT NULL, time_php NUMERIC(8, 4) NOT NULL, time_db NUMERIC(8, 4) NOT NULL, time_end NUMERIC(8, 4) NOT NULL, time_userend NUMERIC(8, 4) NOT NULL, INDEX date_created_idx (date_created, request_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");

		$this->out("Create jira_issues table");
		$db->exec("DROP TABLE IF EXISTS jira_issues");
		$db->exec("DROP TABLE IF EXISTS jira_issue_comments");
		$db->exec("CREATE TABLE jira_issues (id INT AUTO_INCREMENT NOT NULL, ticket_id INT DEFAULT NULL, issue_id INT NOT NULL, created INT NOT NULL, INDEX IDX_88385CE2700047D2 (ticket_id), INDEX issue_id_idx (issue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$db->exec("CREATE TABLE jira_issue_comments (id INT AUTO_INCREMENT NOT NULL, issue_id INT DEFAULT NULL, message_id INT DEFAULT NULL, jira_comment_id INT NOT NULL, INDEX IDX_776438D15E7AA58C (issue_id), UNIQUE INDEX UNIQ_776438D1537A1329 (message_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$db->exec("ALTER TABLE jira_issues ADD CONSTRAINT FK_88385CE2700047D2 FOREIGN KEY (ticket_id) REFERENCES tickets (id) ON DELETE SET NULL");
		$db->exec("ALTER TABLE jira_issue_comments ADD CONSTRAINT FK_776438D15E7AA58C FOREIGN KEY (issue_id) REFERENCES jira_issues (id) ON DELETE SET NULL");
		$db->exec("ALTER TABLE jira_issue_comments ADD CONSTRAINT FK_776438D1537A1329 FOREIGN KEY (message_id) REFERENCES tickets_messages (id) ON DELETE CASCADE");

		$this->out("Remove field client_messages.handler_class");
		$db->exec("ALTER TABLE client_messages DROP handler_class");

		$this->out("Remove field templates.variant_of");
		$db->exec("ALTER TABLE templates DROP variant_of");

		$this->out("Add new ticket_actions_def table");
		$db->exec("CREATE TABLE ticket_actions_def (id INT AUTO_INCREMENT NOT NULL, app_id INT DEFAULT NULL, action_name VARCHAR(50) NOT NULL, def_class VARCHAR(255) DEFAULT NULL, settings LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', INDEX IDX_5FEF87EF7987212D (app_id), UNIQUE INDEX action_name_idx (action_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");

		$this->out("Add new ticket_layouts table");
		$db->exec("CREATE TABLE ticket_layouts (id INT AUTO_INCREMENT NOT NULL, department_id INT DEFAULT NULL, is_enabled TINYINT(1) NOT NULL, user_layout LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', agent_layout LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', date_updated DATETIME NOT NULL, INDEX IDX_59FC2F66AE80F5DF (department_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$db->exec("ALTER TABLE ticket_layouts ADD CONSTRAINT FK_59FC2F66AE80F5DF FOREIGN KEY (department_id) REFERENCES departments (id) ON DELETE CASCADE");

		$this->out("Remove old ticket_page_display table");
		$db->exec("DROP TABLE IF EXISTS ticket_page_display");

		$this->out("Create new ticket_proc_log table");
		$db->exec("CREATE TABLE ticket_proc_log (id INT AUTO_INCREMENT NOT NULL, ticket_id INT DEFAULT NULL, blob_id INT DEFAULT NULL, date_created DATETIME NOT NULL, INDEX IDX_F3CC6D4B700047D2 (ticket_id), INDEX IDX_F3CC6D4BED3E8EA5 (blob_id), INDEX date_created_idx (date_created), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$db->exec("ALTER TABLE ticket_proc_log ADD CONSTRAINT FK_F3CC6D4B700047D2 FOREIGN KEY (ticket_id) REFERENCES tickets (id) ON DELETE CASCADE");
		$db->exec("ALTER TABLE ticket_proc_log ADD CONSTRAINT FK_F3CC6D4BED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE CASCADE");

		$this->out("Create new ticket_proc_log table");
		$db->exec("ALTER TABLE email_sources ADD log_blob_id INT DEFAULT NULL");
		$db->exec("ALTER TABLE email_sources ADD CONSTRAINT FK_6F9D0D3DD5F3B632 FOREIGN KEY (log_blob_id) REFERENCES blobs (id) ON DELETE SET NULL");
		$db->exec("CREATE INDEX IDX_6F9D0D3DD5F3B632 ON email_sources (log_blob_id)");

		$this->out("Add organizations_deleted table");
		$db->exec("CREATE TABLE organizations_deleted (organization_id INT NOT NULL, by_person_id INT DEFAULT NULL, date_created DATETIME NOT NULL, reason LONGTEXT NOT NULL, INDEX IDX_14AE1C5BB5BE2AA2 (by_person_id), PRIMARY KEY(organization_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$db->exec("ALTER TABLE organizations_deleted ADD CONSTRAINT FK_14AE1C5BB5BE2AA2 FOREIGN KEY (by_person_id) REFERENCES people (id) ON DELETE SET NULL");

		$this->out("Add persons_deleted table");
		$db->exec("CREATE TABLE persons_deleted (person_id INT NOT NULL, by_person_id INT DEFAULT NULL, date_created DATETIME NOT NULL, reason LONGTEXT NOT NULL, INDEX IDX_6FF07BCEB5BE2AA2 (by_person_id), PRIMARY KEY(person_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$db->exec("ALTER TABLE persons_deleted ADD CONSTRAINT FK_6FF07BCEB5BE2AA2 FOREIGN KEY (by_person_id) REFERENCES people (id) ON DELETE SET NULL");

		$this->out("Add text_snippet_logs table");
		$db->exec("CREATE TABLE text_snippet_logs (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, ticket_id INT DEFAULT NULL, snippet_id INT DEFAULT NULL, time INT NOT NULL, INDEX IDX_8B657EAB217BBB47 (person_id), INDEX IDX_8B657EAB700047D2 (ticket_id), INDEX IDX_8B657EAB6E34B975 (snippet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$db->exec("ALTER TABLE text_snippet_logs ADD CONSTRAINT FK_8B657EAB217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL");
		$db->exec("ALTER TABLE text_snippet_logs ADD CONSTRAINT FK_8B657EAB700047D2 FOREIGN KEY (ticket_id) REFERENCES tickets (id) ON DELETE SET NULL");
		$db->exec("ALTER TABLE text_snippet_logs ADD CONSTRAINT FK_8B657EAB6E34B975 FOREIGN KEY (snippet_id) REFERENCES text_snippets (id) ON DELETE SET NULL");

		$this->out("Add sessions.date_last_page");
		$this->execMutateSql("ALTER TABLE sessions ADD date_last_page DATETIME NOT NULL");

		$this->out("Change data type of usersources.options");
		$this->execMutateSql("ALTER TABLE usersources CHANGE options options LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)'");
	}
}