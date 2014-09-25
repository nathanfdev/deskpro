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

class Build1411636714 extends AbstractBuild
{
	public function run()
	{
		$this->out("Add jobs, sms_accounts and tickets_sms tables");
		$this->execMutateSql("CREATE TABLE jobs (id INT AUTO_INCREMENT NOT NULL, original_job_id INT DEFAULT NULL, depends_on_job_id INT DEFAULT NULL, type VARCHAR(50) DEFAULT NULL, status VARCHAR(25) DEFAULT NULL, status_code VARCHAR(25) DEFAULT NULL, worker_id VARCHAR(128) DEFAULT NULL, date_touch DATETIME DEFAULT NULL, date_created DATETIME DEFAULT NULL, date_next_try DATETIME DEFAULT NULL, date_last_try DATETIME DEFAULT NULL, priority INT NOT NULL, num_tries INT NOT NULL, data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', log_summary VARCHAR(256) DEFAULT NULL, log LONGTEXT DEFAULT NULL, has_warning TINYINT(1) NOT NULL, INDEX IDX_A8936DC5589C6D79 (original_job_id), INDEX IDX_A8936DC55DA20803 (depends_on_job_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("CREATE TABLE sms_accounts (id INT AUTO_INCREMENT NOT NULL, phone_number_id INT DEFAULT NULL, type VARCHAR(20) NOT NULL, params LONGBLOB DEFAULT NULL COMMENT '(DC2Type:array)', identifier VARCHAR(128) DEFAULT NULL, is_enabled TINYINT(1) NOT NULL, is_connected TINYINT(1) NOT NULL, is_tested TINYINT(1) NOT NULL, test_code VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_AC3EBFAD39DFD528 (phone_number_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("CREATE TABLE tickets_sms (id INT AUTO_INCREMENT NOT NULL, ticket_id INT DEFAULT NULL, person_id INT DEFAULT NULL, sms_account_id INT DEFAULT NULL, job_id INT DEFAULT NULL, date_created DATETIME DEFAULT NULL, from_number VARCHAR(30) DEFAULT NULL, to_number VARCHAR(30) DEFAULT NULL, direction VARCHAR(10) DEFAULT NULL, message LONGTEXT DEFAULT NULL, INDEX IDX_CB28F4B0700047D2 (ticket_id), INDEX IDX_CB28F4B0217BBB47 (person_id), INDEX IDX_CB28F4B0CE191853 (sms_account_id), INDEX IDX_CB28F4B0BE04EA9 (job_id), INDEX date_created_idx (date_created), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("ALTER TABLE jobs ADD CONSTRAINT FK_A8936DC5589C6D79 FOREIGN KEY (original_job_id) REFERENCES jobs (id)");
		$this->execMutateSql("ALTER TABLE jobs ADD CONSTRAINT FK_A8936DC55DA20803 FOREIGN KEY (depends_on_job_id) REFERENCES jobs (id)");
		$this->execMutateSql("ALTER TABLE sms_accounts ADD CONSTRAINT FK_AC3EBFAD39DFD528 FOREIGN KEY (phone_number_id) REFERENCES phone_numbers (id) ON DELETE SET NULL");
		$this->execMutateSql("ALTER TABLE tickets_sms ADD CONSTRAINT FK_CB28F4B0700047D2 FOREIGN KEY (ticket_id) REFERENCES tickets (id)");
		$this->execMutateSql("ALTER TABLE tickets_sms ADD CONSTRAINT FK_CB28F4B0217BBB47 FOREIGN KEY (person_id) REFERENCES people (id)");
		$this->execMutateSql("ALTER TABLE tickets_sms ADD CONSTRAINT FK_CB28F4B0CE191853 FOREIGN KEY (sms_account_id) REFERENCES sms_accounts (id)");
		$this->execMutateSql("ALTER TABLE tickets_sms ADD CONSTRAINT FK_CB28F4B0BE04EA9 FOREIGN KEY (job_id) REFERENCES jobs (id)");
	}
}