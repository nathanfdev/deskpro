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

class Build1407945473 extends AbstractBuild
{
	public function run()
	{
		$this->out("Create the 'jobs' table");
		$this->execMutateSql("CREATE TABLE jobs (id INT AUTO_INCREMENT NOT NULL, original_job_id INT DEFAULT NULL, type VARCHAR(50) DEFAULT NULL, status VARCHAR(25) DEFAULT NULL, status_code VARCHAR(25) DEFAULT NULL, date_touch DATETIME DEFAULT NULL, date_created DATETIME DEFAULT NULL, date_last_try DATETIME DEFAULT NULL, date_next_try DATETIME DEFAULT NULL, priority VARCHAR(255) DEFAULT NULL, num_tries VARCHAR(255) DEFAULT NULL, data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', log_summary VARCHAR(256) DEFAULT NULL, log LONGTEXT DEFAULT NULL, has_warning TINYINT(1) NOT NULL, INDEX IDX_A8936DC5589C6D79 (original_job_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("ALTER TABLE jobs ADD CONSTRAINT FK_A8936DC5589C6D79 FOREIGN KEY (original_job_id) REFERENCES jobs (id)");
	}
}
